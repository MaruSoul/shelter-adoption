{{-- Завантаження й порядок фото. Очікує $existingPhotos, $maxPhotos і властивість $uploads. --}}
<fieldset class="flex flex-col gap-3">
    <legend class="font-semibold mb-2">{{ __('Фото') }} <span class="font-normal text-sm text-muted">{{ __('JPEG, PNG або WebP, до 10 МБ, максимум :n. Перше фото – обкладинка.', ['n' => $maxPhotos]) }}</span></legend>

    @if ($existingPhotos->isNotEmpty())
        <ul class="grid gap-3 grid-cols-[repeat(auto-fill,minmax(140px,1fr))]">
            @foreach ($existingPhotos as $i => $photo)
                <li class="relative rounded-2xl overflow-hidden border border-line bg-card" wire:key="photo-{{ $photo->id }}">
                    <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->alt }}" class="aspect-[4/3] w-full object-cover">
                    @if ($i === 0)<span class="chip absolute left-2 top-2 bg-sun-soft text-accent text-xs">{{ __('Обкладинка') }}</span>@endif
                    <div class="flex justify-between p-1.5">
                        <button type="button" wire:click="movePhoto({{ $photo->id }}, -1)" class="btn-ghost px-3 min-h-9" aria-label="{{ __('Перемістити ліворуч') }}" @disabled($i === 0)>←</button>
                        <button type="button" wire:click="deletePhoto({{ $photo->id }})" wire:confirm="{{ __('Видалити це фото?') }}" class="btn-ghost px-3 min-h-9 text-danger">{{ __('Видалити') }}</button>
                        <button type="button" wire:click="movePhoto({{ $photo->id }}, 1)" class="btn-ghost px-3 min-h-9" aria-label="{{ __('Перемістити праворуч') }}" @disabled($loop->last)>→</button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @if (count($uploads))
        {{-- Прев'ю обраних, але ще не збережених фото --}}
        <ul class="grid gap-3 grid-cols-[repeat(auto-fill,minmax(140px,1fr))]">
            @foreach ($uploads as $i => $file)
                <li class="relative rounded-2xl overflow-hidden border-2 border-dashed border-primary/40 bg-card" wire:key="upload-{{ $i }}-{{ $file->getFilename() }}">
                    @if ($file->isPreviewable())
                        <img src="{{ $file->temporaryUrl() }}" alt="{{ __('Нове фото :n', ['n' => $i + 1]) }}" class="aspect-[4/3] w-full object-cover">
                    @else
                        <div class="aspect-[4/3] w-full bg-tint-1 flex items-center justify-center text-sm text-muted px-2 text-center">{{ $file->getClientOriginalName() }}</div>
                    @endif
                    @if ($existingPhotos->isEmpty() && $i === 0)<span class="chip absolute left-2 top-2 bg-sun-soft text-accent text-xs">{{ __('Обкладинка') }}</span>@endif
                    <span class="chip absolute right-2 top-2 bg-primary text-primary-ink text-xs">{{ __('Нове') }}</span>
                    <div class="flex justify-center p-1.5">
                        <button type="button" wire:click="removeUpload({{ $i }})" class="btn-ghost px-3 min-h-9 text-danger">{{ __('Прибрати') }}</button>
                    </div>
                </li>
            @endforeach
        </ul>
        <p class="text-sm text-muted">{{ __('Нові фото збережуться разом із карткою.') }}</p>
    @endif

    @if ($existingPhotos->count() + count($uploads) < $maxPhotos)
        <label class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-line bg-bg p-6 cursor-pointer hover:border-primary">
            <span class="font-semibold text-primary">{{ count($uploads) ? __('Додати ще фото') : __('Обрати фото') }}</span>
            <span class="text-sm text-muted">{{ __('можна кілька одразу · ще :n', ['n' => $maxPhotos - $existingPhotos->count() - count($uploads)]) }}</span>
            <input type="file" wire:model="newUploads" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
        </label>
        <div wire:loading wire:target="newUploads" class="text-sm text-muted">{{ __('Завантажуємо…') }}</div>
    @endif
    @error('uploads')<span class="error">{{ $message }}</span>@enderror
    @error('newUploads')<span class="error">{{ $message }}</span>@enderror
    @foreach ($errors->get('newUploads.*') as $messages)<span class="error">{{ $messages[0] }}</span>@endforeach
    @error('uploads.*')<span class="error">{{ $message }}</span>@enderror
</fieldset>
