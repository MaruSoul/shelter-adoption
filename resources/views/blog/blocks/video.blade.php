{{-- Блок «video» статті на сайті. Дані готує App\Services\Blocks; $lang – позначка мови неперекладеного блоку. --}}
{{-- Спершу лише обкладинка з кнопкою: плеєр YouTube/Vimeo вантажиться після натиску, щоб сторінка не сповільнювалась --}}
<figure class="flex flex-col gap-2" {{ $lang }} x-data="{ play: false }">
    <div class="relative aspect-video w-full rounded-3xl overflow-hidden bg-ink">
        <template x-if="play">
            <iframe src="{{ $block['data']['embed'] }}" title="{{ $block['data']['caption'] ?? __('Відео') }}" class="absolute inset-0 size-full"
                    allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </template>
        <button type="button" x-show="! play" x-on:click="play = true" class="group absolute inset-0 flex items-center justify-center"
                aria-label="{{ __('Відтворити відео') }}@if (! empty($block['data']['caption'])): {{ $block['data']['caption'] }}@endif">
            @if ($block['data']['thumb'])
                <img src="{{ $block['data']['thumb'] }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover" x-on:error="$el.remove()">
            @endif
            <span class="relative size-20 rounded-full bg-primary text-primary-ink shadow-lg flex items-center justify-center transition group-hover:scale-110" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-9 translate-x-0.5" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
            </span>
        </button>
        {{-- Без JavaScript – звичайне посилання на відео --}}
        <noscript><a href="{{ $block['data']['url'] }}" class="absolute inset-0 flex items-center justify-center text-bg underline">{{ __('Дивитися відео') }}</a></noscript>
    </div>
    @if (! empty($block['data']['caption']))<figcaption class="text-sm text-muted text-center">{{ $block['data']['caption'] }}</figcaption>@endif
</figure>
