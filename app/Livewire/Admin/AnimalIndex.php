<?php

namespace App\Livewire\Admin;

use App\Enums\AnimalSource;
use App\Enums\AnimalStatus;
use App\Models\Animal;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AnimalIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $source = '';

    /** Фільтр «Без перекладу»: картки, де є український текст без англійського. */
    #[Url(except: '')]
    public string $translation = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $animals = Animal::query()
            ->with(['species', 'coverPhoto', 'locality'])
            ->withCount(['applications as active_applications_count' => fn ($q) => $q->active()])
            ->when($this->q, fn ($q) => $q->where(fn ($q) => $q->where('name', 'ilike', '%'.$this->q.'%')->orWhere('name_en', 'ilike', '%'.$this->q.'%')))
            ->when($this->translation === 'missing', fn ($q) => $q->missingTranslation())
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->source, fn ($q) => $q->where('source', $this->source))
            ->latest('id')
            ->paginate(20);

        return view('livewire.admin.animal-index', [
            'animals' => $animals,
            'statuses' => AnimalStatus::cases(),
            'sources' => AnimalSource::cases(),
        ])->title('Тварини');
    }
}
