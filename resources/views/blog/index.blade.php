<x-layout :title="$current ? $current->name : 'Блог'" description="Поради з підготовки до появи тварини вдома, здоровʼя, виховання та новини притулку.">
    <div class="mx-auto max-w-6xl px-6 py-10 flex flex-col gap-8">
        <div class="flex flex-col gap-2">
            <h1 class="text-4xl md:text-5xl">Блог</h1>
            <p class="text-muted">Поради від волонтерів і ветеринарів притулку.</p>
        </div>
        <nav aria-label="Рубрики" class="flex flex-wrap gap-2">
            <a href="{{ route('blog.index') }}" @class(['btn border-[1.5px]', 'bg-primary text-primary-ink border-primary' => ! $current, 'bg-card border-line' => $current]) @if(! $current) aria-current="page" @endif>Усі</a>
            @foreach ($categories as $category)
                <a href="{{ route('blog.index', ['rubric' => $category->slug]) }}" @class(['btn border-[1.5px]', 'bg-primary text-primary-ink border-primary' => $current?->is($category), 'bg-card border-line' => ! $current?->is($category)]) @if($current?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
            @endforeach
        </nav>
        @if ($posts->isEmpty())
            <p class="card p-8 text-muted">У цій рубриці поки немає статей.</p>
        @else
            <div class="grid gap-6 grid-cols-[repeat(auto-fill,minmax(280px,1fr))]">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
            {{ $posts->links() }}
        @endif
    </div>
</x-layout>
