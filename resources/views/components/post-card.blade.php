@props(['post'])
<a href="{{ route('blog.show', $post) }}" class="card card-lift overflow-hidden flex flex-col group" data-reveal>
    <div class="aspect-[1200/630] bg-surface overflow-hidden">
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" loading="lazy" class="size-full object-cover card-photo">
        @endif
    </div>
    <div class="p-5 flex flex-col gap-2">
        <span class="text-sm font-semibold text-accent">{{ $post->category->name }}</span>
        <span class="font-display font-extrabold text-xl leading-tight group-hover:text-primary">{{ $post->title }}</span>
        <span class="text-sm text-muted">
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time> ·
            @endif
            {{ $post->readingMinutes() }} хв
        </span>
    </div>
</a>
