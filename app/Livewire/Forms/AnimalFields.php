<?php

namespace App\Livewire\Forms;

use App\Enums\Sex;
use App\Models\Animal;
use App\Models\Breed;
use App\Models\Locality;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Спільні поля картки тварини для форми волонтера (FR-11) і редактора адміністратора (FR-13).
 */
class AnimalFields extends Form
{
    public string $name = '';

    public string $name_en = '';

    public string $species_id = '';

    public string $breed_id = '';

    public string $color_id = '';

    public string $region_id = '';

    public string $locality_id = '';

    public string $sex = '';

    public string $birth_date = '';

    public bool $is_sterilized = false;

    public bool $is_vaccinated = false;

    public string $story = '';

    public string $story_en = '';

    public string $behavior = '';

    public string $behavior_en = '';

    public string $adoption_terms = '';

    public string $adoption_terms_en = '';

    public string $current_location = '';

    public string $current_location_en = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'name_en' => ['nullable', 'string', 'max:60'],
            'species_id' => ['required', Rule::exists('species', 'id')],
            'breed_id' => ['nullable', Rule::exists('breeds', 'id')->where('species_id', (int) $this->species_id)],
            'color_id' => ['nullable', Rule::exists('colors', 'id')],
            'region_id' => ['required', Rule::exists('regions', 'id')],
            'locality_id' => ['required', Rule::exists('localities', 'id')->where('region_id', (int) $this->region_id)],
            'sex' => ['required', Rule::enum(Sex::class)],
            'birth_date' => ['required', 'date', 'before_or_equal:today', 'after:-30 years'],
            'is_sterilized' => ['boolean'],
            'is_vaccinated' => ['boolean'],
            'story' => ['nullable', 'string', 'max:3000'],
            'behavior' => ['nullable', 'string', 'max:3000'],
            'adoption_terms' => ['nullable', 'string', 'max:3000'],
            'story_en' => ['nullable', 'string', 'max:3000'],
            'behavior_en' => ['nullable', 'string', 'max:3000'],
            'adoption_terms_en' => ['nullable', 'string', 'max:3000'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'current_location_en' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => __('кличка'), 'species_id' => __('вид'), 'breed_id' => __('порода'), 'color_id' => __('забарвлення'),
            'region_id' => __('область'), 'locality_id' => __('населений пункт'), 'sex' => __('стать'),
            'birth_date' => __('дата народження'), 'story' => __('коротка історія'), 'behavior' => __('особливості поведінки'),
            'adoption_terms' => __('умови прилаштування'), 'current_location' => __('де тварина зараз'),
            'name_en' => __('кличка англійською'), 'story_en' => __('коротка історія англійською'),
            'behavior_en' => __('особливості поведінки англійською'), 'adoption_terms_en' => __('умови прилаштування англійською'),
        ];
    }

    public function fillFrom(Animal $animal): void
    {
        $this->name = $animal->name;
        $this->name_en = (string) $animal->name_en;
        $this->species_id = (string) $animal->species_id;
        $this->breed_id = (string) ($animal->breed_id ?? '');
        $this->color_id = (string) ($animal->color_id ?? '');
        $this->locality_id = (string) $animal->locality_id;
        $this->region_id = (string) Locality::whereKey($animal->locality_id)->value('region_id');
        $this->sex = $animal->sex->value;
        $this->birth_date = $animal->birth_date->toDateString();
        $this->is_sterilized = $animal->is_sterilized;
        $this->is_vaccinated = $animal->is_vaccinated;
        $this->story = (string) $animal->story;
        $this->behavior = (string) $animal->behavior;
        $this->adoption_terms = (string) $animal->adoption_terms;
        $this->story_en = (string) $animal->story_en;
        $this->behavior_en = (string) $animal->behavior_en;
        $this->adoption_terms_en = (string) $animal->adoption_terms_en;
        $this->current_location = (string) $animal->current_location;
        $this->current_location_en = (string) $animal->current_location_en;
    }

    /** Переносить перевірені значення в модель (без збереження). */
    public function applyTo(Animal $animal): void
    {
        $data = $this->validate();

        $animal->fill([
            'name' => trim($data['name']),
            'name_en' => trim($data['name_en']) ?: null,
            'species_id' => (int) $data['species_id'],
            'breed_id' => $data['breed_id'] ?: null,
            'color_id' => $data['color_id'] ?: null,
            'locality_id' => (int) $data['locality_id'],
            'sex' => $data['sex'],
            'birth_date' => $data['birth_date'],
            'is_sterilized' => $data['is_sterilized'],
            'is_vaccinated' => $data['is_vaccinated'],
            'story' => trim($data['story']) ?: null,
            'behavior' => trim($data['behavior']) ?: null,
            'adoption_terms' => trim($data['adoption_terms']) ?: null,
            'story_en' => trim($data['story_en']) ?: null,
            'behavior_en' => trim($data['behavior_en']) ?: null,
            'adoption_terms_en' => trim($data['adoption_terms_en']) ?: null,
            'current_location' => trim($data['current_location']) ?: null,
            'current_location_en' => trim($data['current_location_en']) ?: null,
        ]);
    }

    public function breeds()
    {
        return $this->species_id ? Breed::where('species_id', $this->species_id)->orderBy('name')->get() : collect();
    }

    public function localities()
    {
        return $this->region_id ? Locality::where('region_id', $this->region_id)->alphabetical()->get() : collect();
    }
}
