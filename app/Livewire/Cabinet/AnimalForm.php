<?php

namespace App\Livewire\Cabinet;

use App\Enums\AnimalSource;
use App\Enums\AnimalStatus;
use App\Livewire\Concerns\ManagesAnimalPhotos;
use App\Livewire\Forms\AnimalFields;
use App\Models\Animal;
use App\Models\Color;
use App\Models\Region;
use App\Models\Species;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Розміщення знайденої тварини волонтером (FR-11). Нова або змінена картка
 * завжди потрапляє на модерацію і зʼявляється в каталозі лише після схвалення (FR-12).
 */
class AnimalForm extends Component
{
    use ManagesAnimalPhotos;

    public const MAX_PHOTOS = 5;

    public ?Animal $animal = null;

    public AnimalFields $form;

    public function mount(?Animal $animal = null): void
    {
        if ($animal?->exists) {
            $this->authorize('update', $animal);
            $this->animal = $animal;
            $this->form->fillFrom($animal);
        } else {
            $this->animal = new Animal;
        }
    }

    public function save()
    {
        $isNew = ! $this->animal->exists;
        if (! $isNew) {
            $this->authorize('update', $this->animal);
        }

        $this->validate($this->photoRules());
        $this->form->applyTo($this->animal);

        if ($isNew && count($this->uploads) === 0) {
            $this->addError('uploads', __('Додайте хоча б одне фото тварини.'));

            return null;
        }

        DB::transaction(function () use ($isNew) {
            if ($isNew) {
                $this->animal->created_by = auth()->id();
                $this->animal->source = AnimalSource::Volunteer;
            }
            // Будь-яка зміна картки волонтера – повторна модерація
            $this->animal->status = AnimalStatus::Moderation;
            $this->animal->moderation_note = null;
            $this->animal->published_at = null;
            $this->animal->save();

            $this->storeUploads($this->animal);
        });

        if ($isNew) {
            session()->flash('celebrate', true);
        }

        session()->flash('status', $isNew
            ? __('Дякуємо! Картку надіслано на перевірку. Після схвалення вона зʼявиться в каталозі.')
            : __('Зміни збережено. Картка знову на перевірці у модератора.'));

        return $this->redirectRoute('cabinet', navigate: false);
    }

    public function render()
    {
        return view('livewire.cabinet.animal-form', [
            'species' => Species::orderBy('id')->get(),
            'colors' => Color::orderBy('name')->get(),
            'regions' => Region::alphabetical()->get(),
            'existingPhotos' => $this->animal->exists ? $this->animal->photos : collect(),
            'maxPhotos' => self::MAX_PHOTOS,
        ])->title($this->animal->exists ? __('Редагування картки') : __('Розмістити тварину'));
    }
}
