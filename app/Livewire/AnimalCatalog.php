<?php

namespace App\Livewire;

use App\Enums\AgeGroup;
use App\Enums\Sex;
use App\Models\Animal;
use App\Models\Breed;
use App\Models\Color;
use App\Models\Locality;
use App\Models\Region;
use App\Models\Species;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Каталог «Знайти друга» (FR-01). Усі фільтри зберігаються в адресі сторінки,
 * тож посиланням на відфільтрований каталог можна поділитися.
 */
class AnimalCatalog extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $species = '';

    #[Url(except: [])]
    public array $sex = [];

    #[Url(except: [])]
    public array $age = [];

    #[Url(except: false)]
    public bool $sterilized = false;

    #[Url(except: false)]
    public bool $vaccinated = false;

    #[Url(except: '')]
    public string $region = '';

    #[Url(except: '')]
    public string $locality = '';

    #[Url(except: '')]
    public string $breed = '';

    #[Url(except: '')]
    public string $color = '';

    #[Url(except: false)]
    public bool $adopted = false;

    #[Url(except: 'new')]
    public string $sort = 'new';

    public function updated(string $property): void
    {
        // Зміна виду скидає породу, зміна області – населений пункт
        if ($property === 'species') {
            $this->breed = '';
        }
        if ($property === 'region') {
            $this->locality = '';
        }
        $this->resetPage();
    }

    public function setSpecies(string $slug): void
    {
        $this->species = $slug;
        $this->breed = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['species', 'sex', 'age', 'sterilized', 'vaccinated', 'region', 'locality', 'breed', 'color', 'adopted', 'sort']);
        $this->resetPage();
    }

    #[Computed]
    public function animals()
    {
        $query = Animal::query()
            ->with(['species', 'locality', 'coverPhoto'])
            ->filter([
                'species' => $this->species ?: null,
                'sex' => array_values(array_intersect($this->sex, array_column(Sex::cases(), 'value'))),
                'age' => $this->age,
                'sterilized' => $this->sterilized,
                'vaccinated' => $this->vaccinated,
                'region' => $this->region ?: null,
                'locality' => $this->locality ?: null,
                'breed' => $this->breed ?: null,
                'color' => $this->color ?: null,
                'adopted' => $this->adopted,
            ]);

        match ($this->sort) {
            'young' => $query->orderByDesc('birth_date'),
            'old' => $query->orderBy('birth_date'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };

        return $query->paginate(12);
    }

    public function render()
    {
        $speciesModel = $this->species ? Species::firstWhere('slug', $this->species) : null;

        return view('livewire.animal-catalog', [
            'speciesList' => Species::orderBy('id')->get(),
            'ageGroups' => AgeGroup::cases(),
            'sexes' => Sex::cases(),
            'regions' => Region::whereHas('localities')->orderBy('name')->get(),
            'localities' => $this->region ? Locality::where('region_id', $this->region)->orderBy('name')->get() : collect(),
            'breeds' => $speciesModel ? Breed::where('species_id', $speciesModel->id)->orderBy('name')->get() : collect(),
            'colors' => Color::orderBy('name')->get(),
            'activeCount' => $this->activeFiltersCount(),
        ])->title($speciesModel ? $speciesModel->name_plural.' шукають дім' : 'Знайти друга');
    }

    private function activeFiltersCount(): int
    {
        return count(array_filter([
            $this->species, $this->sex, $this->age, $this->sterilized, $this->vaccinated,
            $this->region, $this->locality, $this->breed, $this->color, $this->adopted,
        ]));
    }
}
