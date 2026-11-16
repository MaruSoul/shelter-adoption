<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Обрані тварини. Гість зберігає список у браузері (localStorage), а сторінка підтягує картки за id.
 * Користувач, який увійшов, зберігає обрані в базі; після входу обрані з браузера переносяться в акаунт.
 */
class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $animals = $request->user()
            ? $request->user()->favorites()->visible()->with(['species', 'locality', 'coverPhoto'])->get()
            : null; // для гостя картки підвантажує сторінка

        return view('favorites.index', ['animals' => $animals]);
    }

    /** HTML карток для гостя: лише публічні тварини з переданих id. */
    public function cards(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))->map(fn ($id) => (int) $id)->filter()->unique()->take(100);

        $animals = Animal::visible()->whereIn('id', $ids)->with(['species', 'locality', 'coverPhoto'])->get()
            ->sortBy(fn ($a) => $ids->search($a->id))->values();

        return view('favorites.cards', ['animals' => $animals]);
    }

    public function toggle(Request $request, int $animalId): JsonResponse
    {
        $animal = Animal::visible()->findOrFail($animalId);
        $result = $request->user()->favorites()->toggle($animal->id);

        return response()->json(['favorite' => in_array($animal->id, $result['attached'], true)]);
    }

    /** Переносить обрані з браузера гостя в акаунт після входу. */
    public function sync(Request $request): JsonResponse
    {
        $ids = collect($request->input('ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->take(100);
        $valid = Animal::visible()->whereIn('id', $ids)->pluck('id');

        $request->user()->favorites()->syncWithoutDetaching($valid);

        return response()->json(['ids' => $request->user()->favorites()->pluck('animals.id')]);
    }
}
