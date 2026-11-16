<?php

namespace App\Livewire\Admin;

use App\Enums\AnimalStatus;
use App\Models\Animal;
use Livewire\Component;

/** Модерація карток, розміщених волонтерами (FR-12). */
class ModerationQueue extends Component
{
    public ?string $flash = null;

    /** @var array<int, string> причини відхилення за id тварини */
    public array $reasons = [];

    public function approve(int $id): void
    {
        $animal = $this->pending($id);
        $animal->status = AnimalStatus::Available;
        $animal->moderation_note = null;
        $animal->published_at = now();
        $animal->save();

        $this->flash = $animal->name.' тепер у каталозі.';
    }

    public function reject(int $id): void
    {
        $this->validate(
            ["reasons.$id" => ['required', 'string', 'min:5', 'max:500']],
            ["reasons.$id.required" => 'Вкажіть причину – волонтер побачить її у кабінеті.', "reasons.$id.min" => 'Опишіть причину трохи детальніше.'],
        );

        $animal = $this->pending($id);
        $animal->status = AnimalStatus::Rejected;
        $animal->moderation_note = trim($this->reasons[$id]);
        $animal->save();
        unset($this->reasons[$id]);

        $this->flash = 'Картку «'.$animal->name.'» відхилено.';
    }

    private function pending(int $id): Animal
    {
        $this->authorize('moderate', Animal::class);

        return Animal::where('status', AnimalStatus::Moderation)->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.admin.moderation-queue', [
            'animals' => Animal::where('status', AnimalStatus::Moderation)
                ->with(['photos', 'species', 'breed', 'color', 'locality.region', 'author'])
                ->oldest('updated_at')
                ->get(),
        ])->title('Модерація');
    }
}
