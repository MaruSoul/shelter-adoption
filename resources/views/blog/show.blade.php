{{-- Стаття без англійського заголовка англійською версією показувала б український текст – таку сторінку не індексуємо --}}
<x-layout :title="$post->tr('title')" :seo-title="$post->seo('title')" :description="$post->seo('description') ?: $post->tr('excerpt')" :image="$post->coverUrl()"
          :alternates="filled($post->title_en)" :robots="app()->getLocale() === 'en' && blank($post->title_en) ? 'noindex, follow' : null">
    <x-slot:head>
        <x-json-ld :data="\App\Support\StructuredData::article($post, \App\Models\ShelterSetting::current())" />
        <x-json-ld :data="\App\Support\StructuredData::breadcrumbs([__('Блог') => route('blog.index'), $post->category->label() => route('blog.index', ['rubric' => $post->category->slug]), $post->tr('title') => route('blog.show', $post)])" />
    </x-slot:head>
    <article class="mx-auto max-w-3xl px-6 py-10 flex flex-col gap-6">
        <nav aria-label="{{ __('Навігаційний ланцюжок') }}" class="text-sm text-muted flex flex-wrap gap-2">
            <a href="{{ route('blog.index') }}" class="hover:underline">{{ __('Блог') }}</a><span aria-hidden="true">/</span>
            <a href="{{ route('blog.index', ['rubric' => $post->category->slug]) }}" class="hover:underline">{{ $post->category->label() }}</a>
        </nav>
        @unless ($post->isPublished())
            <div role="status" class="rounded-2xl bg-sun-soft px-5 py-3 font-medium">{{ __('Чернетка – цю статтю бачите лише ви як адміністратор.') }}</div>
        @endunless
        <header class="flex flex-col gap-3">
            <h1 class="text-4xl md:text-5xl leading-tight" @unless ($post->isShownInLocale('title')) lang="uk" @endunless>{{ $post->tr('title') }}</h1>
            <p class="text-muted flex flex-wrap gap-x-2">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time><span aria-hidden="true">·</span>
                @endif
                <span>{{ __(':n хв читання', ['n' => $post->readingMinutes()]) }}</span>
            </p>
        </header>
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" class="w-full rounded-3xl aspect-[1200/630] object-cover">
        @endif
        @if (app()->getLocale() === 'en' && ($state = $post->englishState()) !== 'full')
            <p class="text-sm text-muted">{{ $state === 'none' ? __('This article is available in Ukrainian only.') : __('Some parts of this article are available in Ukrainian only.') }}</p>
        @endif
        <x-post-blocks :blocks="$blocks" />
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related" class="mx-auto max-w-6xl px-6 pb-6 flex flex-col gap-6">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 id="related" class="text-3xl">{{ __('Читайте також') }}</h2>
                <a href="{{ route('blog.index') }}" class="font-semibold text-primary hover:underline">{{ __('Усі статті →') }}</a>
            </div>
            <div class="grid gap-6 grid-cols-[repeat(auto-fill,minmax(280px,1fr))]">
                @foreach ($related as $item)
                    <x-post-card :post="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
