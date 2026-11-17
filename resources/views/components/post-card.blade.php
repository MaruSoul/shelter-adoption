@props(['post', 'eager' => false])
<a href="{{ route('blog.show', $post) }}" class="card card-lift overflow-hidden flex flex-col group" data-reveal>
    <div class="aspect-[1200/630] bg-surface overflow-hidden">
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" width="1200" height="630" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async" class="size-full object-cover card-photo">
        @endif
    </div>
    <div class="p-5 flex flex-col gap-2">
        <span class="text-sm font-semibold text-accent">{{ __($post->category->name) }}</span>
        <span class="font-display font-extrabold text-xl leading-tight group-hover:text-primary">{{ $post->title }}</span>
        <span class="text-sm text-muted">
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time> ·
            @endif
            {{ __(':n хв', ['n' => $post->readingMinutes()]) }}
        </span>
    </div>
</a>
