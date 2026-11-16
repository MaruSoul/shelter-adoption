@if ($paginator->hasPages())
    <nav aria-label="Сторінки" class="flex flex-wrap justify-center gap-2 pt-3">
        @if (! $paginator->onFirstPage())
            <button type="button" wire:click="previousPage" class="size-11 rounded-xl border border-line bg-card font-semibold" aria-label="Попередня сторінка">←</button>
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
                        <button type="button" wire:click="gotoPage({{ $page }})" class="size-11 rounded-xl border border-line bg-card font-semibold">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage" class="size-11 rounded-xl border border-line bg-card font-semibold" aria-label="Наступна сторінка">→</button>
        @endif
    </nav>
@endif
