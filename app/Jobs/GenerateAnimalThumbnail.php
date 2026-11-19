<?php

namespace App\Jobs;

use App\Models\AnimalPhoto;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Мініатюра 600×450 для карток каталогу (NFR-06). Виконується у фоні,
 * щоб завантаження фото не затримувало відповідь сторінки.
 */
class GenerateAnimalThumbnail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public AnimalPhoto $photo) {}

    public function handle(): void
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($this->photo->path)) {
            return;
        }

        $thumb = Image::decodePath($disk->path($this->photo->path))
            ->cover(600, 450)
            ->encode(new JpegEncoder(quality: 78, strip: true));

        $thumbPath = preg_replace('/\.jpg$/', '-thumb.jpg', $this->photo->path);
        $disk->put($thumbPath, (string) $thumb);

        $this->photo->update(['thumb_path' => $thumbPath]);
    }
}
