{{-- Завантаження й порядок фото. Очікує $existingPhotos, $maxPhotos і властивість $uploads. --}}
<fieldset class="flex flex-col gap-3">
    <legend class="font-semibold mb-2">Фото <span class="font-normal text-sm text-muted">JPEG, PNG або WebP, до 10 МБ, максимум {{ $maxPhotos }}. Перше фото – обкладинка.</span></legend>

    @if ($existingPhotos->isNotEmpty())
        <ul class="grid gap-3 grid-cols-[repeat(auto-fill,minmax(140px,1fr))]">
            @foreach ($existingPhotos as $i => $photo)
                <li class="relative rounded-2xl overflow-hidden border border-line bg-card" wire:key="photo-{{ $photo->id }}">
                    <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->alt }}" class="aspect-[4/3] w-full object-cover">
                    @if ($i === 0)<span class="chip absolute left-2 top-2 bg-sun text-sun-ink text-xs">Обкладинка</span>@endif
                    <div class="flex justify-between p-1.5">
                        <button type="button" wire:click="movePhoto({{ $photo->id }}, -1)" class="btn-ghost px-3 min-h-9" aria-label="Перемістити ліворуч" @disabled($i === 0)>←</button>
                        <button type="button" wire:click="deletePhoto({{ $photo->id }})" wire:confirm="Видалити це фото?" class="btn-ghost px-3 min-h-9 text-danger">Видалити</button>
                        <button type="button" wire:click="movePhoto({{ $photo->id }}, 1)" class="btn-ghost px-3 min-h-9" aria-label="Перемістити праворуч" @disabled($loop->last)>→</button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($existingPhotos->count() < $maxPhotos)
        <label class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-line bg-bg p-6 cursor-pointer hover:border-primary">
            <span class="font-semibold text-primary">Обрати фото</span>
            <span class="text-sm text-muted">можна кілька одразу</span>
            <input type="file" wire:model="uploads" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
        </label>
        <div wire:loading wire:target="uploads" class="text-sm text-muted">Завантажуємо…</div>
        @if (count($uploads))
            <p class="text-sm text-muted">Обрано файлів: {{ count($uploads) }}. Вони збережуться разом із карткою.</p>
        @endif
    @endif
    @error('uploads')<span class="error">{{ $message }}</span>@enderror
    @error('uploads.*')<span class="error">{{ $message }}</span>@enderror
</fieldset>
