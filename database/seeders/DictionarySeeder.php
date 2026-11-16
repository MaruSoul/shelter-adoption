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

        $this->seedLocalities();

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

    /**
     * Міста й селища всіх областей з КАТОТТГ (database/seeders/data, джерело – README.md там же).
     * Додаються лише відсутні записи, тож сідер можна безпечно запускати на вже заповненій базі.
     */
    private function seedLocalities(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/localities.json')), true, flags: JSON_THROW_ON_ERROR);
        $regions = Region::pluck('id', 'name');

        foreach ($data as $regionName => $names) {
            $regionId = $regions[$regionName] ?? null;
            if (! $regionId) {
                continue;
            }

            $existing = Locality::where('region_id', $regionId)->pluck('name')->flip();
            $rows = collect($names)
                ->reject(fn ($name) => $existing->has($name))
                ->map(fn ($name) => ['region_id' => $regionId, 'name' => $name, 'created_at' => now(), 'updated_at' => now()])
                ->values();

            $rows->chunk(500)->each(fn ($chunk) => Locality::insert($chunk->all()));
        }
    }
}
