@props(['state', 'coverage' => null])
{{-- Стан англійської версії в списках адмінки: full / partial / none. --}}
@php([$done, $total] = $coverage ?? [0, 0])
@switch($state)
    @case('full')
        <span class="chip bg-success-soft text-success whitespace-nowrap" title="Англійська версія заповнена">✓ EN</span>
        @break
    @case('partial')
        <span class="chip bg-sun-soft text-accent whitespace-nowrap" title="Перекладено {{ $done }} з {{ $total }} фрагментів">EN {{ $done }}/{{ $total }}</span>
        @break
    @default
        <span class="chip bg-sun-soft text-accent whitespace-nowrap" title="Є текст без англійського перекладу">без EN</span>
@endswitch
