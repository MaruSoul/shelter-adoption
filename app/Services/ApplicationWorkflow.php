<?php

namespace App\Services;

use App\Enums\AnimalStatus;
use App\Enums\ApplicationStatus;
use App\Exceptions\AnimalAlreadyAdoptedException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\ApplicationStatusChange;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Зміна статусів анкети за матрицею переходів (FR-07) та прилаштування тварини (FR-08).
 */
class ApplicationWorkflow
{
    /**
     * Переводить анкету в новий статус. Для «Прилаштовано» виконує повний алгоритм прилаштування.
     */
    public function transition(AdoptionApplication $application, ApplicationStatus $to, User $actor, ?string $note = null): AdoptionApplication
    {
        if ($to === ApplicationStatus::Adopted) {
            return $this->adopt($application, $actor, $note);
        }

        return DB::transaction(function () use ($application, $to, $actor, $note) {
            // Блокуємо рядок анкети, щоб два адміністратори не змінили її одночасно
            $locked = AdoptionApplication::query()->lockForUpdate()->findOrFail($application->id);

            $this->assertAllowed($locked->status, $to);
            $this->apply($locked, $to, $actor, $note);

            return $locked;
        });
    }

    /**
     * Алгоритм прилаштування:
     *   1) у транзакції блокуємо рядки тварини й анкети (SELECT … FOR UPDATE);
     *   2) перевіряємо перехід за матрицею і що тварина ще шукає дім;
     *   3) анкета → «Прилаштовано», тварина → «Знайшла дім»;
     *   4) усі інші активні анкети на цю тварину → «Закрито» з поясненням;
     *   5) частковий унікальний індекс БД гарантує, що другої «Прилаштовано» не буде навіть за гонки.
     */
    public function adopt(AdoptionApplication $application, User $actor, ?string $note = null): AdoptionApplication
    {
        try {
            return DB::transaction(function () use ($application, $actor, $note) {
                $animal = Animal::query()->lockForUpdate()->findOrFail($application->animal_id);
                $locked = AdoptionApplication::query()->lockForUpdate()->findOrFail($application->id);

                $this->assertAllowed($locked->status, ApplicationStatus::Adopted);

                if ($animal->status !== AnimalStatus::Available) {
                    throw new AnimalAlreadyAdoptedException('Тварина вже не шукає дім.');
                }

                $this->apply($locked, ApplicationStatus::Adopted, $actor, $note);

                $animal->status = AnimalStatus::Adopted;
                $animal->adopted_at = now();
                $animal->save();

                $others = AdoptionApplication::query()
                    ->where('animal_id', $animal->id)
                    ->whereKeyNot($locked->id)
                    ->active()
                    ->lockForUpdate()
                    ->get();

                foreach ($others as $other) {
                    $this->apply($other, ApplicationStatus::Closed, $actor, 'Тварину прилаштовано за іншою анкетою.');
                }

                return $locked;
            });
        } catch (UniqueConstraintViolationException) {
            throw new AnimalAlreadyAdoptedException('Тварину вже прилаштовано за іншою анкетою.');
        }
    }

    /**
     * Закриває всі активні анкети на тварину, картку якої видалив її автор.
     */
    public function closeForRemovedAnimal(Animal $animal, User $actor): void
    {
        $active = AdoptionApplication::query()
            ->where('animal_id', $animal->id)
            ->active()
            ->lockForUpdate()
            ->get();

        foreach ($active as $application) {
            $this->apply($application, ApplicationStatus::Closed, $actor, 'Автор оголошення прибрав картку тварини з сайту.');
        }
    }

    public function assertAllowed(ApplicationStatus $from, ApplicationStatus $to): void
    {
        if (! $from->canTransitionTo($to)) {
            throw InvalidStatusTransitionException::between($from, $to);
        }
    }

    private function apply(AdoptionApplication $application, ApplicationStatus $to, User $actor, ?string $note): void
    {
        $from = $application->status;
        $application->status = $to;
        $application->save();

        ApplicationStatusChange::create([
            'adoption_application_id' => $application->id,
            'user_id' => $actor->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }
}
