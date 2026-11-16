<?php

namespace Database\Seeders\Support;

use App\Enums\Sex;
use App\Jobs\GenerateAnimalThumbnail;
use App\Models\Animal;
use App\Models\AnimalPhoto;
use App\Models\Breed;
use App\Models\Color;
use App\Models\Locality;
use App\Models\Region;
use App\Models\Species;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Демонстраційні тварини з database/seeders/photos/pets/{cats,dogs}/N:
 * кожна папка – одна тварина з info.json (вигадані дані) і серією її фото з Unsplash (джерела – CREDITS.md).
 */
final class DemoPets
{
    /** Обкладинки, вибрані вручну там, де перше фото невдале (тварини не видно або дрібно). */
    private const COVERS = [
        'dogs/1' => 'alvan-nee-FkyQXrnvWI',
        'cats/4' => 'hang-niu-i4vGvr0YU4c',
        'cats/10' => 'mikhail-vasilyev-j5Z',
        'cats/12' => 'timo-volz-ZlFKIG6dAp',
    ];

    /** @return list<array{key: string, species: string, info: array, photos: list<string>}> */
    public static function all(): array
    {
        $base = database_path('seeders/photos/pets');
        $pets = [];

        foreach (['cats' => 'cat', 'dogs' => 'dog'] as $dir => $species) {
            $folders = glob("$base/$dir/*", GLOB_ONLYDIR) ?: [];
            usort($folders, fn ($a, $b) => (int) basename($a) <=> (int) basename($b));

            foreach ($folders as $folder) {
                $info = json_decode(file_get_contents("$folder/info.json"), true, flags: JSON_THROW_ON_ERROR);
                $photos = glob("$folder/*.jpg") ?: [];
                sort($photos);

                // Фото з reference_photos – першими: перше з них стає обкладинкою
                $preferred = array_map(fn ($name) => self::normalizeName($name), $info['reference_photos'] ?? []);
                usort($photos, function ($a, $b) use ($preferred) {
                    $ia = array_search(basename($a), $preferred, true);
                    $ib = array_search(basename($b), $preferred, true);

                    return ($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib);
                });

                $key = $dir.'/'.basename($folder);
                if (isset(self::COVERS[$key])) {
                    usort($photos, fn ($a, $b) => (int) ! str_starts_with(basename($a), self::COVERS[$key])
                        <=> (int) ! str_starts_with(basename($b), self::COVERS[$key]));
                }

                $pets[] = ['key' => $key, 'species' => $species, 'info' => $info, 'photos' => $photos];
            }
        }

        return $pets;
    }

    /** Атрибути моделі Animal з info.json. */
    public static function attributes(array $pet): array
    {
        $info = $pet['info'];
        $species = Species::firstWhere('slug', $pet['species']);
        $region = Region::firstWhere('name', Str::of($info['region'])->replace(' область', '')->toString());

        return [
            'species_id' => $species->id,
            'breed_id' => Breed::firstOrCreate(['species_id' => $species->id, 'name' => self::breed($info['breed'])])->id,
            'color_id' => Color::firstOrCreate(['name' => self::baseColor($info['color'])])->id,
            'locality_id' => Locality::firstOrCreate(['region_id' => $region->id, 'name' => $info['settlement']])->id,
            'name' => $info['name'],
            'sex' => $info['sex'] === 'Самка' ? Sex::Female : Sex::Male,
            'birth_date' => $info['approximate_birth_date'],
            'is_sterilized' => (bool) $info['sterilized'],
            'is_vaccinated' => (bool) $info['vaccinated'],
            'current_location' => $info['current_location'],
            'story' => $info['short_history'],
            'behavior' => $info['behavior_traits'],
            'adoption_terms' => $info['adoption_conditions'],
        ];
    }

    /** Копіює всі фото тварини у сховище і одразу робить мініатюри. */
    public static function attachPhotos(Animal $animal, array $pet): void
    {
        foreach ($pet['photos'] as $order => $source) {
            $path = 'animals/'.$animal->id.'/'.basename($source);
            Storage::disk('public')->put($path, file_get_contents($source));

            $photo = AnimalPhoto::create([
                'animal_id' => $animal->id,
                'path' => $path,
                'alt' => $animal->name.', фото '.($order + 1),
                'sort_order' => $order,
            ]);
            (new GenerateAnimalThumbnail($photo))->handle();
        }
    }

    /** «Бігль (імовірно)» → «Бігль», «Без породи / невідомо» → «Без породи». */
    public static function breed(string $raw): string
    {
        if (str_starts_with($raw, 'Без породи')) {
            return 'Без породи';
        }

        return trim(preg_replace('/\s*\((?:[^()]*,\s*)?імовірно\)|\s*\(([^,()]+),\s*імовірно\)/u', '', $raw));
    }

    /**
     * Детальний опис забарвлення з анкети → базовий колір довідника (для фільтра каталогу).
     * Порядок правил важливий: «Триколірне» і «смугасте» перевіряються раніше за кольори.
     */
    public static function baseColor(string $raw): string
    {
        $c = mb_strtolower($raw);

        return match (true) {
            str_contains($c, 'триколір') => 'Триколірне',
            str_contains($c, 'смуг'), str_contains($c, 'тигров') => 'Смугасте',
            str_contains($c, 'мерль'), str_contains($c, 'срібл'), str_starts_with($c, 'сір') => 'Сіре',
            str_starts_with($c, 'чорно-біл') => 'Чорно-біле',
            str_starts_with($c, 'чорн') => 'Чорне',
            str_starts_with($c, 'кремов'), str_starts_with($c, 'палев') => 'Палеве',
            str_starts_with($c, 'коричнев') => 'Коричневе',
            str_starts_with($c, 'біл'), str_starts_with($c, 'рудо-біл'), str_starts_with($c, 'бленгейм') => 'Біле з рудим',
            default => 'Руде',
        };
    }

    private static function normalizeName(string $file): string
    {
        return preg_replace('/(-unsplash)?(\(\d+\))?\.\w+$/', '', $file).'.jpg';
    }
}
