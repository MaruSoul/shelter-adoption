<?php

namespace App\Livewire\Admin;

use App\Enums\AnimalStatus;
use App\Enums\ApplicationStatus;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'stats' => [
                ['Нові анкети', AdoptionApplication::where('status', ApplicationStatus::New)->count(), route('admin.applications.index', ['status' => 'new'])],
                ['Анкети в роботі', AdoptionApplication::active()->where('status', '!=', ApplicationStatus::New)->count(), route('admin.applications.index', ['status' => 'in_progress'])],
                ['На модерації', Animal::where('status', AnimalStatus::Moderation)->count(), route('admin.moderation')],
                ['Шукають дім', Animal::where('status', AnimalStatus::Available)->count(), route('admin.animals.index', ['status' => 'available'])],
                ['Прилаштовано за 30 днів', Animal::where('status', AnimalStatus::Adopted)->where('adopted_at', '>=', now()->subDays(30))->count(), route('admin.animals.index', ['status' => 'adopted'])],
            ],
            'latest' => AdoptionApplication::with('animal')->where('status', ApplicationStatus::New)->latest()->take(6)->get(),
        ])->title('Панель притулку');
    }
}
