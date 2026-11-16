<?php

namespace App\Livewire\Admin;

use App\Enums\ApplicationStatus;
use App\Models\AdoptionApplication;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Список анкет з фільтрами й пошуком (FR-06). */
class ApplicationIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $animal = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $applications = AdoptionApplication::query()
            ->with(['animal', 'region'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->animal, fn ($q) => $q->whereHas('animal', fn ($a) => $a->where('name', 'ilike', '%'.$this->animal.'%')))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->search($this->q)
            ->latest()
            ->paginate(20);

        return view('livewire.admin.application-index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::cases(),
        ])->title('Анкети');
    }
}
