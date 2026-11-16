<?php

namespace App\Http\Controllers;

use App\Enums\AnimalStatus;
use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    /**
     * Сторінка тварини (FR-02). Тварина на модерації чи відхилена – 404 для всіх,
     * крім автора картки й адміністратора.
     */
    public function show(Request $request, Animal $animal)
    {
        if (! $animal->isPublic() && ! $request->user()?->can('preview', $animal)) {
            abort(404);
        }

        $animal->load(['species', 'breed', 'color', 'locality.region', 'photos']);

        $similar = Animal::query()
            ->where('status', AnimalStatus::Available)
            ->where('species_id', $animal->species_id)
            ->whereKeyNot($animal->id)
            ->with(['species', 'locality', 'coverPhoto'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('animals.show', compact('animal', 'similar'));
    }
}
