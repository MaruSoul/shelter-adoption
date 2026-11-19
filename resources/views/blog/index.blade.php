{{-- SEO-налаштування блогу діють на головній сторінці блогу; рубрики й наступні сторінки – з типовими заголовками --}}
@php($seo = \App\Models\SeoPage::for('blog'))
@php($mainBlogPage = ! $current && ! request()->has('page'))
<x-layout :title="$current ? $current->label() : __('Блог')" :seo-title="$mainBlogPage ? $seo->tr('title') : null"
          :description="($mainBlogPage ? $seo->tr('description') : null) ?: __('Поради з підготовки до появи тварини вдома, здоровʼя, виховання та новини притулку.')">
    <div class="mx-auto max-w-6xl px-6 py-10 flex flex-col gap-8">
        <div class="flex flex-col gap-2">
            <h1 class="text-4xl md:text-5xl">{{ ($mainBlogPage ? $seo->tr('h1') : null) ?: __('Блог') }}</h1>
            <p class="text-muted">{{ __('Поради від волонтерів і ветеринарів притулку.') }}</p>
        </div>
        <nav aria-label="{{ __('Рубрики') }}" class="flex flex-wrap gap-2">
            <a href="{{ route('blog.index') }}" @class(['btn border-[1.5px]', 'bg-primary text-primary-ink border-primary' => ! $current, 'bg-card border-line' => $current]) @if(! $current) aria-current="page" @endif>{{ __('Усі') }}</a>
            @foreach ($categories as $category)
                <a href="{{ route('blog.index', ['rubric' => $category->slug]) }}" @class(['btn border-[1.5px]', 'bg-primary text-primary-ink border-primary' => $current?->is($category), 'bg-card border-line' => ! $current?->is($category)]) @if($current?->is($category)) aria-current="page" @endif>{{ $category->label() }}</a>
            @endforeach
        </nav>
        @if ($posts->isEmpty())
            <p class="card p-8 text-muted">{{ __('У цій рубриці поки немає статей.') }}</p>
        @else
            <div class="grid gap-6 grid-cols-[repeat(auto-fill,minmax(280px,1fr))]">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" :eager="$loop->first && $posts->onFirstPage()" />
                @endforeach
            </div>
            {{ $posts->links() }}
        @endif
    </div>
    @if ($mainBlogPage)
        <x-seo-text :seo="$seo" />
    @endif
</x-layout>
