<?php

namespace App\Http\Controllers;

use App\Enums\AnimalStatus;
use App\Models\Animal;
use App\Models\Post;
use App\Models\Species;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('home', [
            'animals' => Animal::query()
                ->where('status', AnimalStatus::Available)
                ->with(['species', 'locality', 'coverPhoto'])
                ->latest('published_at')
                ->take(8)
                ->get(),
            'lottery' => $this->lotteryPool(),
            'posts' => Post::published()->with('category')->latest('published_at')->take(3)->get(),
            'species' => Species::orderBy('id')->get(),
            'adoptedCount' => Animal::where('status', AnimalStatus::Adopted)->count(),
            'waitingCount' => Animal::where('status', AnimalStatus::Available)->count(),
        ]);
    }

    /**
     * Тварини для гри «Хто мій друг?» на головній: усі, хто зараз шукає дім і має фото.
     * Список невеликий, тому віддаємо його одразу, а випадковий вибір робить браузер.
     *
     * @return list<array<string, string>>
     */
    private function lotteryPool(): array
    {
        return Animal::query()
            ->where('status', AnimalStatus::Available)
            ->whereHas('photos')
            ->with('coverPhoto')
            ->get()
            ->map(fn (Animal $a) => [
                'name' => $a->name,
                'meta' => $a->sex->label().', '.$a->ageLabel(),
                'photo' => $a->coverPhoto->url(),
                'url' => route('animals.show', $a, false),
                'adopt' => route('animals.adopt', $a, false),
            ])
            ->values()
            ->all();
    }
}
