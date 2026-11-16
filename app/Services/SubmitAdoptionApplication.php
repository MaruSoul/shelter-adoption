<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Exceptions\DuplicateApplicationException;
use App\Mail\ApplicationReceivedMail;
use App\Mail\NewApplicationMail;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\ApplicationStatusChange;
use App\Models\ShelterSetting;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Подання анкети «Взяти додому» без обовʼязкової реєстрації (FR-03 – FR-05).
 *
 * Вхід: перевірені дані форми. Вихід: створена анкета зі статусом «Нова».
 * Обмеження: тварина має шукати дім; одна активна анкета з одного e-mail на тварину.
 */
class SubmitAdoptionApplication
{
    /**
     * @param  array{name: string, email: string, phone: string, region_id: int, contact_channels: list<string>, social_url?: ?string, comment?: ?string}  $data
     */
    public function handle(Animal $animal, array $data, ?User $user, ?string $ip): AdoptionApplication
    {
        $email = mb_strtolower(trim($data['email']));

        if ($this->hasActiveDuplicate($animal, $email)) {
            throw new DuplicateApplicationException;
        }

        try {
            $application = DB::transaction(function () use ($animal, $data, $user, $ip, $email) {
                $application = new AdoptionApplication([
                    ...$data,
                    'email' => $email,
                    'animal_id' => $animal->id,
                    // Привʼязуємо до акаунта лише якщо адресу підтверджено і вона збігається
                    'user_id' => $user?->hasVerifiedEmail() && $user->email === $email ? $user->id : null,
                    'consent_at' => now(),
                    'ip_address' => $ip,
                ]);
                $application->status = ApplicationStatus::New;
                $application->save();

                ApplicationStatusChange::create([
                    'adoption_application_id' => $application->id,
                    'from_status' => null,
                    'to_status' => ApplicationStatus::New,
                ]);

                return $application;
            });
        } catch (UniqueConstraintViolationException) {
            // Паралельний дублікат, який перехопив частковий унікальний індекс БД
            throw new DuplicateApplicationException;
        }

        $this->notify($application);

        return $application;
    }

    public function hasActiveDuplicate(Animal $animal, string $email): bool
    {
        return AdoptionApplication::query()
            ->where('animal_id', $animal->id)
            ->where('email', mb_strtolower(trim($email)))
            ->active()
            ->exists();
    }

    private function notify(AdoptionApplication $application): void
    {
        $recipient = ShelterSetting::current()->notification_email;

        if ($recipient) {
            Mail::to($recipient)->queue(new NewApplicationMail($application));
        }

        Mail::to($application->email)->queue(new ApplicationReceivedMail($application));
    }
}
