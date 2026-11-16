<x-layout :title="$post->title" :description="$post->excerpt" :image="$post->coverUrl()">
    <x-slot:head>
        <x-json-ld :data="\App\Support\StructuredData::article($post, \App\Models\ShelterSetting::current())" />
        <x-json-ld :data="\App\Support\StructuredData::breadcrumbs(['Блог' => route('blog.index'), $post->category->name => route('blog.index', ['rubric' => $post->category->slug]), $post->title => route('blog.show', $post)])" />
    </x-slot:head>
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
            <p class="text-muted flex flex-wrap gap-x-2">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time><span aria-hidden="true">·</span>
                @endif
                <span>{{ $post->readingMinutes() }} хв читання</span>
            </p>
        </header>
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" class="w-full rounded-3xl aspect-[1200/630] object-cover">
        @endif
        <x-post-blocks :blocks="$blocks" />
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related" class="mx-auto max-w-6xl px-6 pb-6 flex flex-col gap-6">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 id="related" class="text-3xl">Читайте також</h2>
                <a href="{{ route('blog.index') }}" class="font-semibold text-primary hover:underline">Усі статті →</a>
            </div>
            <div class="grid gap-6 grid-cols-[repeat(auto-fill,minmax(280px,1fr))]">
                @foreach ($related as $item)
                    <x-post-card :post="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
