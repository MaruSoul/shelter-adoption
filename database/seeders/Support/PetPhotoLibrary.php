<?php

namespace Database\Seeders\Support;

use App\Jobs\GenerateAnimalThumbnail;
use App\Models\Animal;
use App\Models\AnimalPhoto;
use Illuminate\Support\Facades\Storage;

/**
 * Справжні фото котів і собак для демонстраційних даних (database/seeders/photos, джерела – CREDITS.md).
 * Кожне фото видається лише один раз, щоб різні тварини не мали однакових знімків.
 * Коли фото певного виду закінчуються, використовується нейтральна заглушка.
 */
final class PetPhotoLibrary
{
    /** @var array<string, list<string>> */
    private array $pool = [];

    public function __construct()
    {
        $base = database_path('seeders/photos');
        foreach (['cat' => 'cats', 'dog' => 'dogs'] as $species => $dir) {
            $files = glob("$base/$dir/*.jpg") ?: [];
            sort($files);
            $this->pool[$species] = $files;
        }
    }

    public function remaining(string $species): int
    {
        return count($this->pool[$species] ?? []);
    }

    /** Додає тварині наступне вільне фото її виду. */
    public function attach(Animal $animal, int $sortOrder = 0): AnimalPhoto
    {
        $species = $animal->species->slug;
        $source = array_shift($this->pool[$species]);

        if ($source !== null) {
            $path = 'animals/'.$animal->id.'/'.basename($source);
            Storage::disk('public')->put($path, file_get_contents($source));
        } else {
            $path = PlaceholderPhoto::make('animals/'.$animal->id, $animal->id + $sortOrder);
        }

        $photo = AnimalPhoto::create([
            'animal_id' => $animal->id,
            'path' => $path,
            'alt' => $animal->name.', фото '.($sortOrder + 1),
            'sort_order' => $sortOrder,
        ]);

        // Мініатюру генеруємо одразу, без черги
        (new GenerateAnimalThumbnail($photo))->handle();

        return $photo->refresh();
    }
}
