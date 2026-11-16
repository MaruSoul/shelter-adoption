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
            'posts' => Post::published()->with('category')->latest('published_at')->take(3)->get(),
            'species' => Species::orderBy('id')->get(),
            'adoptedCount' => Animal::where('status', AnimalStatus::Adopted)->count(),
            'waitingCount' => Animal::where('status', AnimalStatus::Available)->count(),
        ]);
    }
}
