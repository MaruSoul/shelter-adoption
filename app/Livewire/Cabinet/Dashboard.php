<?php

namespace App\Livewire\Cabinet;

use App\Services\RemoveAnimal;
use Livewire\Component;

/**
 * Кабінет користувача (FR-10): власні анкети зі статусами і власні розміщені тварини.
 * Дані завжди беруться через звʼязки поточного користувача, тому чужих записів тут бути не може.
 */
class Dashboard extends Component
{
    public string $tab = 'applications';

    public ?string $flash = null;

    public function deleteAnimal(int $id, RemoveAnimal $remover): void
    {
        // Шукаємо лише серед власних карток: чужу знайти неможливо
        $animal = auth()->user()->animals()->findOrFail($id);
        $this->authorize('delete', $animal);

        $remover->handle($animal, auth()->user());

        $this->tab = 'animals';
        $this->flash = __('Картку «:name» видалено.', ['name' => $animal->display_name]);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.cabinet.dashboard', [
            'applications' => $user->applications()->with(['animal.coverPhoto', 'statusChanges'])->latest()->get(),
            'animals' => $user->animals()->with(['coverPhoto', 'species'])->latest()->get(),
        ])->title('Кабінет');
    }
}
