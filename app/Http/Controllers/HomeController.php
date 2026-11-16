<?php

namespace App\Http\Controllers;

use App\Enums\AgeGroup;
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
            'quickPicks' => $this->quickPicks(),
            'adoptedCount' => Animal::where('status', AnimalStatus::Adopted)->count(),
            'waitingCount' => Animal::where('status', AnimalStatus::Available)->count(),
        ]);
    }

    /**
     * Готові добірки замість форми швидкого пошуку: один дотик – і відкривається каталог із фільтром.
     * Порожні добірки не показуємо.
     *
     * @return list<array{label: string, hint: string, url: string, count: int}>
     */
    private function quickPicks(): array
    {
        $picks = [];
        foreach (Species::orderBy('id')->get() as $species) {
            $picks[] = ['label' => $species->name_plural, 'hint' => 'усі '.mb_strtolower($species->name_plural), 'filters' => ['species' => $species->slug]];
        }
        $picks[] = ['label' => 'Малюки', 'hint' => 'до року', 'filters' => ['age' => [AgeGroup::UpToOne->value]]];
        $picks[] = ['label' => 'Старшенькі', 'hint' => 'понад 5 років', 'filters' => ['age' => [AgeGroup::OverFive->value]]];
        $picks[] = ['label' => 'Готові до переїзду', 'hint' => 'стерилізовані й щеплені', 'filters' => ['sterilized' => true, 'vaccinated' => true]];

        return collect($picks)
            ->map(fn (array $p) => [
                'label' => $p['label'],
                'hint' => $p['hint'],
                'url' => route('animals.index', array_map(fn ($v) => is_bool($v) ? 1 : $v, $p['filters'])),
                'count' => Animal::query()->filter($p['filters'])->count(),
            ])
            ->filter(fn (array $p) => $p['count'] > 0)
            ->values()
            ->all();
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
                'id' => $a->id,
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
