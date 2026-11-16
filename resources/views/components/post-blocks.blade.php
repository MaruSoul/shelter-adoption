@props(['blocks'])
{{-- Рендеринг блоків статті. Увесь текст виводиться через {{ }} і тому екранується. --}}
<div class="flex flex-col gap-5 text-lg leading-relaxed">
    @foreach ($blocks as $block)
        @switch($block['type'])
            @case('heading')
                @if (($block['data']['level'] ?? 2) === 3)
                    <h3 class="text-2xl pt-2">{{ $block['data']['text'] }}</h3>
                @else
                    <h2 class="text-3xl pt-3">{{ $block['data']['text'] }}</h2>
                @endif
                @break
            @case('paragraph')
                <p class="whitespace-pre-line">{{ $block['data']['text'] }}</p>
                @break
            @case('image')
                <figure class="flex flex-col gap-2">
                    <img src="{{ $block['data']['url'] }}" alt="{{ $block['data']['alt'] }}" loading="lazy" class="w-full rounded-3xl">
                    @if (! empty($block['data']['caption']))<figcaption class="text-sm text-muted text-center">{{ $block['data']['caption'] }}</figcaption>@endif
                </figure>
                @break
            @case('quote')
                <blockquote class="rounded-3xl bg-sun-soft px-7 py-6 flex flex-col gap-2">
                    <p class="font-display font-bold text-2xl leading-snug">«{{ $block['data']['text'] }}»</p>
                    @if (! empty($block['data']['author']))<footer class="text-muted">– {{ $block['data']['author'] }}</footer>@endif
                </blockquote>
                @break
            @case('list')
                @if ($block['data']['style'] === 'ordered')
                    <ol class="list-decimal pl-6 flex flex-col gap-1.5">@foreach ($block['data']['items'] as $item)<li>{{ $item }}</li>@endforeach</ol>
                @else
                    <ul class="list-disc pl-6 flex flex-col gap-1.5 marker:text-primary">@foreach ($block['data']['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                @endif
                @break
            @case('animal')
                <div class="max-w-xs"><x-animal-card :animal="$block['animal']" /></div>
                @break
        @endswitch
    @endforeach
</div>
