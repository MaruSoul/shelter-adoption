<x-layout :title="$post->title" :description="$post->excerpt" :image="$post->coverUrl()">
    <article class="mx-auto max-w-3xl px-6 py-10 flex flex-col gap-6">
        <nav aria-label="Навігаційний ланцюжок" class="text-sm text-muted flex flex-wrap gap-2">
            <a href="{{ route('blog.index') }}" class="hover:underline">Блог</a><span aria-hidden="true">/</span>
            <a href="{{ route('blog.index', ['rubric' => $post->category->slug]) }}" class="hover:underline">{{ $post->category->name }}</a>
        </nav>
        @unless ($post->isPublished())
            <div role="status" class="rounded-2xl bg-sun-soft px-5 py-3 font-medium">Чернетка – цю статтю бачите лише ви як адміністратор.</div>
        @endunless
        <header class="flex flex-col gap-3">
            <h1 class="text-4xl md:text-5xl leading-tight">{{ $post->title }}</h1>
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}" class="text-muted">{{ $post->published_at->translatedFormat('j F Y') }}</time>
            @endif
        </header>
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" class="w-full rounded-3xl aspect-[1200/630] object-cover">
        @endif
        <x-post-blocks :blocks="$blocks" />
    </article>
</x-layout>
