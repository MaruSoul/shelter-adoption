@props(['blocks'])
{{-- Рендеринг блоків статті. Увесь текст виводиться через {{ }} і тому екранується. --}}
<div class="flex flex-col gap-5 text-lg leading-relaxed">
    @foreach ($blocks as $block)
        {{-- Блок без перекладу показується українською: позначаємо мову для екранних читачів --}}
        @php($lang = isset($block['lang']) ? 'lang='.$block['lang'] : '')
        @switch($block['type'])
            @case('heading')
                @if (($block['data']['level'] ?? 2) === 3)
                    <h3 class="text-2xl pt-2" {{ $lang }}>{{ $block['data']['text'] }}</h3>
                @else
                    <h2 class="text-3xl pt-3" {{ $lang }}>{{ $block['data']['text'] }}</h2>
                @endif
                @break
            @case('paragraph')
                <p class="whitespace-pre-line" {{ $lang }}>{{ $block['data']['text'] }}</p>
                @break
            @case('image')
                <figure class="flex flex-col gap-2" {{ $lang }}>
                    <img src="{{ $block['data']['url'] }}" alt="{{ $block['data']['alt'] }}" loading="lazy" class="w-full rounded-3xl">
                    @if (! empty($block['data']['caption']))<figcaption class="text-sm text-muted text-center">{{ $block['data']['caption'] }}</figcaption>@endif
                </figure>
                @break
            @case('quote')
                <blockquote class="rounded-3xl bg-sun-soft px-7 py-6 flex flex-col gap-2" {{ $lang }}>
                    <p class="font-display font-bold text-2xl leading-snug">{{ ($block['lang'] ?? app()->getLocale()) === 'en' ? '“' : '«' }}{{ $block['data']['text'] }}{{ ($block['lang'] ?? app()->getLocale()) === 'en' ? '”' : '»' }}</p>
                    @if (! empty($block['data']['author']))<footer class="text-muted">– {{ $block['data']['author'] }}</footer>@endif
                </blockquote>
                @break
            @case('list')
                @if ($block['data']['style'] === 'ordered')
                    <ol class="list-decimal pl-6 flex flex-col gap-1.5" {{ $lang }}>@foreach ($block['data']['items'] as $item)<li>{{ $item }}</li>@endforeach</ol>
                @else
                    <ul class="list-disc pl-6 flex flex-col gap-1.5 marker:text-primary" {{ $lang }}>@foreach ($block['data']['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                @endif
                @break
            @case('animal')
                <div class="max-w-xs"><x-animal-card :animal="$block['animal']" /></div>
                @break
            @case('video')
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
                @break
        @endswitch
    @endforeach
</div>
