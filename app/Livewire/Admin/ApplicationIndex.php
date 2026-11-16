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

    /** Усі анкети однієї людини: фільтр за точною адресою пошти. */
    #[Url(except: '')]
    public string $email = '';

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
            ->when($this->email, fn ($q) => $q->where('email', mb_strtolower($this->email)))
            ->when($this->animal, fn ($q) => $q->whereHas('animal', fn ($a) => $a->where('name', 'ilike', '%'.$this->animal.'%')))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->search($this->q)
            ->latest()
            ->paginate(20);

        // Скільки всього анкет у кожної людини на сторінці – щоб показати «ще N анкет»
        $perPerson = AdoptionApplication::query()
            ->whereIn('email', $applications->pluck('email')->unique())
            ->selectRaw('email, count(*) as total')
            ->groupBy('email')
            ->pluck('total', 'email');

        return view('livewire.admin.application-index', [
            'applications' => $applications,
            'perPerson' => $perPerson,
            'statuses' => ApplicationStatus::cases(),
        ])->title('Анкети');
    }
}
