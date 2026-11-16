@if ($paginator->hasPages())
    <nav aria-label="Сторінки" class="flex flex-wrap justify-center gap-2 pt-3">
        @if (! $paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" class="size-11 rounded-xl border border-line bg-card font-semibold flex items-center justify-center" aria-label="Попередня сторінка">←</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="size-11 flex items-center justify-center text-muted">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="size-11 rounded-xl bg-primary text-primary-ink flex items-center justify-center font-semibold">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="size-11 rounded-xl border border-line bg-card font-semibold flex items-center justify-center">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="size-11 rounded-xl border border-line bg-card font-semibold flex items-center justify-center" aria-label="Наступна сторінка">→</a>
        @endif
    </nav>
@endif
