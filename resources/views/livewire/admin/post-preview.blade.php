{{-- Попередній перегляд статті в конструкторі; рендериться мовою, обраною в перемикачі перегляду. --}}
<article class="card p-6 md:p-10 flex flex-col gap-5" lang="{{ app()->getLocale() }}">
    <h2 class="text-4xl">{{ $title ?: 'Без заголовка' }}</h2>
    <x-post-blocks :blocks="$blocks" />
</article>
