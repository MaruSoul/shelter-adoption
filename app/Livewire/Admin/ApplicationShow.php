<?php

namespace App\Livewire\Admin;

use App\Enums\ApplicationStatus;
use App\Exceptions\AnimalAlreadyAdoptedException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\AdoptionApplication;
use App\Services\ApplicationWorkflow;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Картка анкети: контакти, нотатки, зміна статусу, історія (FR-06 – FR-08). */
class ApplicationShow extends Component
{
    public ?string $flash = null;

    #[Locked]
    public int $applicationId;

    public string $notes = '';

    public string $transitionNote = '';

    public function mount(AdoptionApplication $application): void
    {
        $this->applicationId = $application->id;
        $this->notes = (string) $application->admin_notes;
    }

    public function application(): AdoptionApplication
    {
        return AdoptionApplication::with(['animal.coverPhoto', 'animal.species', 'animal.locality', 'region', 'statusChanges.user', 'user'])->findOrFail($this->applicationId);
    }

    public function saveNotes(): void
    {
        $this->validate(['notes' => ['nullable', 'string', 'max:5000']]);
        $application = $this->application();
        $application->admin_notes = trim($this->notes) ?: null;
        $application->save();
        $this->flash = 'Нотатки збережено.';
    }

    public function changeStatus(string $status, ApplicationWorkflow $workflow): void
    {
        $target = ApplicationStatus::tryFrom($status);
        if (! $target) {
            return;
        }

        if ($target === ApplicationStatus::Declined) {
            $this->validate(['transitionNote' => ['required', 'string', 'max:500']], ['transitionNote.required' => 'Вкажіть причину відмови – вона збережеться в історії.']);
        }

        try {
            $workflow->transition($this->application(), $target, auth()->user(), trim($this->transitionNote) ?: null);
        } catch (InvalidStatusTransitionException|AnimalAlreadyAdoptedException $e) {
            $this->addError('transition', $e->getMessage());

            return;
        }

        $this->transitionNote = '';
        $this->flash = 'Статус змінено: '.$target->label().'.';
    }

    public function render()
    {
        $application = $this->application();

        return view('livewire.admin.application-show', [
            'application' => $application,
            'others' => AdoptionApplication::where('animal_id', $application->animal_id)->whereKeyNot($application->id)->latest()->get(),
        ])->title('Анкета: '.$application->name);
    }
}
