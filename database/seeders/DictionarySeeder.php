<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Color;
use App\Models\Locality;
use App\Models\Region;
use App\Models\Species;
use Illuminate\Database\Seeder;

/**
 * Довідники: реальні області України, основні населені пункти, види, породи, забарвлення.
 */
class DictionarySeeder extends Seeder
{
    private const REGIONS = [
        'Вінницька', 'Волинська', 'Дніпропетровська', 'Донецька', 'Житомирська', 'Закарпатська',
        'Запорізька', 'Івано-Франківська', 'Київська', 'Кіровоградська', 'Луганська', 'Львівська',
        'Миколаївська', 'Одеська', 'Полтавська', 'Рівненська', 'Сумська', 'Тернопільська',
        'Харківська', 'Херсонська', 'Хмельницька', 'Черкаська', 'Чернівецька', 'Чернігівська',
        'м. Київ', 'Автономна Республіка Крим',
    ];

    private const LOCALITIES = [
        'Одеська' => ['Одеса', 'Чорноморськ', 'Южне', 'Біляївка', 'Овідіополь', 'Білгород-Дністровський', 'Ізмаїл'],
        'Миколаївська' => ['Миколаїв', 'Вознесенськ'],
        'м. Київ' => ['Київ'],
    ];

    private const BREEDS = [
        'cat' => ['Без породи', 'Британська короткошерста', 'Шотландська висловуха', 'Сіамська', 'Мейн-кун'],
        'dog' => ['Без породи', 'Лабрадор-ретривер', 'Німецька вівчарка', 'Хаскі', 'Такса', 'Джек-рассел-терʼєр'],
    ];

    private const COLORS = [
        'Чорне', 'Біле', 'Руде', 'Сіре', 'Смугасте', 'Триколірне', 'Біле з рудим', 'Чорно-біле', 'Палеве', 'Коричневе',
    ];

    public function run(): void
    {
        foreach (self::REGIONS as $name) {
            Region::firstOrCreate(['name' => $name]);
        }

        foreach (self::LOCALITIES as $region => $localities) {
            $regionId = Region::where('name', $region)->value('id');
            foreach ($localities as $name) {
                Locality::firstOrCreate(['region_id' => $regionId, 'name' => $name]);
            }
        }

        $species = [
            'cat' => Species::firstOrCreate(['slug' => 'cat'], ['name' => 'Котик', 'name_plural' => 'Котики']),
            'dog' => Species::firstOrCreate(['slug' => 'dog'], ['name' => 'Песик', 'name_plural' => 'Песики']),
        ];

        foreach (self::BREEDS as $slug => $breeds) {
            foreach ($breeds as $name) {
                Breed::firstOrCreate(['species_id' => $species[$slug]->id, 'name' => $name]);
            }
        }

        foreach (self::COLORS as $name) {
            Color::firstOrCreate(['name' => $name]);
        }
    }
}
