<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Видалення картки тварини її автором.
 *
 * Якщо анкет на тварину ще не було, картка й фото видаляються повністю.
 * Якщо анкети були, картка ховається з сайту (soft delete), а активні анкети закриваються,
 * щоб заявники бачили зрозумілий статус, а притулок зберіг історію.
 */
class RemoveAnimal
{
    public function __construct(private ApplicationWorkflow $workflow) {}

    public function handle(Animal $animal, User $actor): void
    {
        $files = DB::transaction(function () use ($animal, $actor) {
            $locked = Animal::query()->lockForUpdate()->findOrFail($animal->id);

            if ($locked->applications()->exists()) {
                $this->workflow->closeForRemovedAnimal($locked, $actor);
                $locked->delete();

                return [];
            }

            $files = $locked->photos->flatMap(fn ($photo) => [$photo->path, $photo->thumb_path])->filter()->all();
            $locked->forceDelete(); // рядки фото видаляються каскадом

            return $files;
        });

        // Файли прибираємо лише після успішної транзакції
        Storage::disk('public')->delete($files);
    }
}
