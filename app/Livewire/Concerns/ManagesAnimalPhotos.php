<?php

namespace App\Livewire\Concerns;

use App\Models\Animal;
use App\Models\AnimalPhoto;
use App\Services\PhotoStorage;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Завантаження, видалення й порядок фото тварини у формах волонтера та адміністратора.
 * Компонент має властивість $animal і константу MAX_PHOTOS.
 */
trait ManagesAnimalPhotos
{
    use WithFileUploads;

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    /** Щойно обрані файли: додаються до $uploads, а не замінюють попередній вибір. */
    public array $newUploads = [];

    protected function photoRules(): array
    {
        $existing = $this->animal?->exists ? $this->animal->photos()->count() : 0;

        return [
            'uploads' => ['array', 'max:'.max(0, static::MAX_PHOTOS - $existing)],
            'uploads.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    public function updatedNewUploads(): void
    {
        $existing = $this->animal?->exists ? $this->animal->photos()->count() : 0;
        $left = max(0, static::MAX_PHOTOS - $existing - count($this->uploads));

        $this->validate([
            'newUploads' => ['array', 'max:'.$left],
            'newUploads.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ], [
            'newUploads.max' => $left === 0 ? __('Більше фото додати не можна.') : __('Можна додати ще не більше :max фото.'),
            'newUploads.*.max' => __('Кожне фото має бути не більшим за 10 МБ.'),
            'newUploads.*.mimes' => __('Підтримуються лише JPEG, PNG і WebP.'),
            'newUploads.*.image' => __('Файл має бути зображенням.'),
        ]);

        $this->uploads = [...$this->uploads, ...$this->newUploads];
        $this->newUploads = [];
    }

    /**
     * Прибирає ще не збережене фото зі списку обраних.
     * Не «removeUpload»: це імʼя Livewire резервує під власний механізм файлів,
     * і виклик із кнопки потрапляв туди замість цього методу (помилка 500).
     */
    public function discardUpload(int $index): void
    {
        unset($this->uploads[$index]);
        $this->uploads = array_values($this->uploads);
    }

    public function updatedUploads(): void
    {
        $this->validate($this->photoRules(), [
            'uploads.max' => __('Можна додати ще не більше :max фото.'),
            'uploads.*.max' => __('Кожне фото має бути не більшим за 10 МБ.'),
            'uploads.*.mimes' => __('Підтримуються лише JPEG, PNG і WebP.'),
            'uploads.*.image' => __('Файл має бути зображенням.'),
        ]);
    }

    /** Зберігає обрані фото після збереження картки. */
    protected function storeUploads(Animal $animal): void
    {
        $storage = app(PhotoStorage::class);
        $next = (int) $animal->photos()->max('sort_order') + ($animal->photos()->exists() ? 1 : 0);

        foreach ($this->uploads as $file) {
            $storage->store($file, $animal, $next++);
        }

        $this->uploads = [];
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = $this->ownedPhoto($photoId);
        app(PhotoStorage::class)->delete($photo);
        $this->afterPhotosChanged();
    }

    /** Зсуває фото на одну позицію ліворуч (-1) або праворуч (+1). */
    public function movePhoto(int $photoId, int $direction): void
    {
        $photo = $this->ownedPhoto($photoId);
        $photos = $this->animal->photos()->get()->values();
        $index = $photos->search(fn ($p) => $p->id === $photo->id);
        $target = $index + ($direction < 0 ? -1 : 1);

        if ($target < 0 || $target >= $photos->count()) {
            return;
        }

        $ordered = $photos->all();
        [$ordered[$index], $ordered[$target]] = [$ordered[$target], $ordered[$index]];

        DB::transaction(function () use ($ordered) {
            foreach ($ordered as $position => $p) {
                $p->update(['sort_order' => $position]);
            }
        });

        $this->afterPhotosChanged();
    }

    protected function afterPhotosChanged(): void
    {
        $this->animal->unsetRelation('photos');
    }

    private function ownedPhoto(int $photoId): AnimalPhoto
    {
        abort_unless($this->animal?->exists, 404);
        $this->authorize('update', $this->animal);

        return $this->animal->photos()->whereKey($photoId)->firstOrFail();
    }
}
