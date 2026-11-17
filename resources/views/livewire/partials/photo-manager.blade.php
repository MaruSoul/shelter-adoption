{{--
    Завантаження й порядок фото. Очікує $existingPhotos, $maxPhotos і властивість $uploads.
    Обрані файли надсилаються по одному (resources/js/photo-uploader.js): так кілька великих фото
    з телефона не впираються в ліміт розміру одного запиту, а збій одного файла не скасовує решту.
--}}
@php($left = $maxPhotos - $existingPhotos->count() - count($uploads))
<fieldset class="flex flex-col gap-3" x-data="photoUploader($wire)" data-left="{{ $left }}">
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
                    <span class="absolute inset-x-2 top-2 flex flex-wrap justify-between gap-1">
                        @if ($existingPhotos->isEmpty() && $i === 0)<span class="chip bg-sun-soft text-accent text-xs">{{ __('Обкладинка') }}</span>@endif
                        <span class="chip bg-primary text-primary-ink text-xs ml-auto">{{ __('Нове') }}</span>
                    </span>
                    <div class="flex justify-center p-1.5">
                        <button type="button" wire:click="discardUpload({{ $i }})" class="btn-ghost px-3 min-h-9 text-danger">{{ __('Прибрати') }}</button>
                    </div>
                </li>
            @endforeach
        </ul>
        <p class="text-sm text-muted">{{ __('Нові фото збережуться разом із карткою.') }}</p>
    @endif

    @if ($left > 0)
        <label class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-line bg-bg p-6 cursor-pointer hover:border-primary"
               :class="busy && 'opacity-60 pointer-events-none'">
            <span class="font-semibold text-primary">{{ count($uploads) ? __('Додати ще фото') : __('Обрати фото') }}</span>
            <span class="text-sm text-muted">{{ __('можна кілька одразу · ще :n', ['n' => $left]) }}</span>
            <input type="file" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" x-on:change="pick($event)" :disabled="busy">
        </label>
    @endif
    <div x-show="busy" x-cloak class="flex flex-col gap-1.5" role="status" aria-live="polite">
        <span class="text-sm text-muted">{{ __('Завантажуємо фото') }} <span x-text="Math.min(done + 1, total)"></span> {{ __('з') }} <span x-text="total"></span>…</span>
        <span class="h-2 rounded-full bg-surface overflow-hidden"><span class="block h-full bg-primary transition-[width]" :style="`width: ${((done + progress / 100) / total) * 100}%`"></span></span>
    </div>
    <span x-show="skipped > 0" x-cloak class="error">{{ __('Обрано більше фото, ніж можна додати; зайві пропущено:') }} <span x-text="skipped"></span></span>
    <span x-show="failed.length" x-cloak class="error">{{ __('Не вдалося завантажити:') }} <span x-text="failed.join(', ')"></span></span>
    @error('uploads')<span class="error">{{ $message }}</span>@enderror
    @error('newUploads')<span class="error">{{ $message }}</span>@enderror
    @foreach ($errors->get('newUploads.*') as $messages)<span class="error">{{ $messages[0] }}</span>@endforeach
    @error('uploads.*')<span class="error">{{ $message }}</span>@enderror
</fieldset>
