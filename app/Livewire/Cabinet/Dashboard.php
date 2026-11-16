<?php

namespace App\Livewire\Cabinet;

use Livewire\Component;

/**
 * Кабінет користувача (FR-10): власні анкети зі статусами і власні розміщені тварини.
 * Дані завжди беруться через звʼязки поточного користувача, тому чужих записів тут бути не може.
 */
class Dashboard extends Component
{
    public string $tab = 'applications';

    public function render()
    {
        $user = auth()->user();

        return view('livewire.cabinet.dashboard', [
            'applications' => $user->applications()->with(['animal.coverPhoto', 'statusChanges'])->latest()->get(),
            'animals' => $user->animals()->with(['coverPhoto', 'species'])->latest()->get(),
        ])->title('Кабінет');
    }
}
