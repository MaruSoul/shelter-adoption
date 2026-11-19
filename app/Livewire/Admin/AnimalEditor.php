<?php

namespace App\Livewire\Admin;

use App\Enums\AnimalSource;
use App\Enums\AnimalStatus;
use App\Livewire\Concerns\ManagesAnimalPhotos;
use App\Livewire\Concerns\TransliteratesFields;
use App\Livewire\Forms\AnimalFields;
use App\Models\Animal;
use App\Models\Color;
use App\Models\Region;
use App\Models\Species;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Створення й редагування тварини притулку адміністратором (FR-13).
 * Тварину з анкетами не видаляють – лише переносять в архів.
 */
class AnimalEditor extends Component
{
    use ManagesAnimalPhotos, TransliteratesFields;

    protected function canTransliterate(string $field): bool
    {
        return $field === 'form.name';
    }

    public const MAX_PHOTOS = 10;

    public Animal $animal;

    public AnimalFields $form;

    public string $status = 'available';

    public function mount(?Animal $animal = null): void
    {
        $this->animal = $animal?->exists ? $animal : new Animal;
        if ($this->animal->exists) {
            $this->form->fillFrom($this->animal);
            $this->status = $this->animal->status->value;
        }
    }

    /** Статуси, які адміністратор може обрати вручну. «Знайшла дім» ставиться лише через анкету. */
    public function selectableStatuses(): array
    {
        return [AnimalStatus::Available, AnimalStatus::Archived];
    }

    public function save()
    {
        // Статус обирається лише для тварин притулку; у картки волонтера, прилаштованої чи відхиленої тварини
        // поля статусу у формі немає, тож і перевіряти нічого (інакше таку картку неможливо було зберегти)
        $statusLocked = $this->animal->exists
            && in_array($this->animal->status, [AnimalStatus::Adopted, AnimalStatus::Moderation, AnimalStatus::Rejected], true);
        $this->validate($this->photoRules() + ($statusLocked ? [] : [
            'status' => ['required', 'in:'.implode(',', array_column($this->selectableStatuses(), 'value'))],
        ]));
        $this->form->applyTo($this->animal);
        $isNew = ! $this->animal->exists;

        DB::transaction(function () use ($isNew) {
            if ($isNew) {
                $this->animal->source = AnimalSource::Shelter;
                $this->animal->created_by = auth()->id();
            }
            // Статус прилаштованої тварини чи картки на модерації тут не змінюється
            if (! in_array($this->animal->status, [AnimalStatus::Adopted, AnimalStatus::Moderation, AnimalStatus::Rejected], true)) {
                $this->animal->status = AnimalStatus::from($this->status);
            }
            if ($this->animal->status === AnimalStatus::Available) {
                $this->animal->published_at ??= now();
            }
            $this->animal->save();
            $this->storeUploads($this->animal);
        });

        // Картку волонтера адміністратор редагує з черги модерації – туди ж і повертаємо, щоб одразу схвалити
        if ($this->animal->status === AnimalStatus::Moderation) {
            session()->flash('status', 'Зміни збережено. Тепер картку «'.$this->animal->name.'» можна схвалити.');

            return $this->redirectRoute('admin.moderation');
        }

        session()->flash('status', $isNew ? 'Тварину додано.' : 'Зміни збережено.');

        return $this->redirectRoute('admin.animals.edit', $this->animal);
    }

    public function render()
    {
        return view('livewire.admin.animal-editor', [
            'species' => Species::orderBy('id')->get(),
            'colors' => Color::orderBy('name')->get(),
            'regions' => Region::alphabetical()->get(),
            'existingPhotos' => $this->animal->exists ? $this->animal->photos : collect(),
            'maxPhotos' => self::MAX_PHOTOS,
            'statuses' => $this->selectableStatuses(),
        ])->title($this->animal->exists ? 'Редагування: '.$this->animal->name : 'Нова тварина');
    }
}
