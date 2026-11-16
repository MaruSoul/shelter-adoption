<?php

namespace App\Services;

use App\Jobs\GenerateAnimalThumbnail;
use App\Models\Animal;
use App\Models\AnimalPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Збереження фото тварин (FR-11, FR-13, NFR-08).
 *
 * Кожне фото перекодовується: повертається за EXIF-орієнтацією, зменшується до 1600 px
 * і зберігається без метаданих (зокрема без GPS-координат місця зйомки).
 * Мініатюра для каталогу генерується окремо у фоновій черзі.
 */
class PhotoStorage
{
    public const MAX_SIDE = 1600;

    public function store(UploadedFile $file, Animal $animal, int $sortOrder): AnimalPhoto
    {
        $encoded = Image::decodePath($file->getRealPath())
            ->orient()
            ->scaleDown(self::MAX_SIDE, self::MAX_SIDE)
            ->encode(new JpegEncoder(quality: 82, strip: true));

        $path = 'animals/'.$animal->id.'/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, (string) $encoded);

        $photo = $animal->photos()->create([
            'path' => $path,
            'alt' => $animal->name.', фото '.($sortOrder + 1),
            'sort_order' => $sortOrder,
        ]);

        GenerateAnimalThumbnail::dispatch($photo);

        return $photo;
    }

    public function delete(AnimalPhoto $photo): void
    {
        Storage::disk('public')->delete(array_filter([$photo->path, $photo->thumb_path]));
        $photo->delete();
    }
}
