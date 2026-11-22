@props(['value', 'label' => null, 'text' => null, 'iconOnlyOnMobile' => false])
{{-- Копіює значення в буфер обміну; на кнопці на 2 секунди зʼявляється «Скопійовано» --}}
@php($text ??= __('Копіювати'))
<button type="button"
        x-data="{ copied: false }"
        x-on:click="navigator.clipboard.writeText(@js($value)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
        aria-label="{{ __('Копіювати') }}{{ $label ? ': '.$label : '' }}"
        {{ $attributes->class('shrink-0 inline-flex items-center justify-center gap-1.5 rounded-full border-[1.5px] border-line bg-card min-h-10 text-sm font-semibold transition-colors hover:border-primary', $iconOnlyOnMobile ? 'min-w-10 px-2.5 sm:px-4' : 'px-3') }}>
    <svg x-show="! copied" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
    <svg x-show="copied" x-cloak viewBox="0 0 24 24" class="size-4 text-success" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg>
    <span @class(['hidden sm:inline' => $iconOnlyOnMobile]) x-text="copied ? @js(__('Скопійовано')) : @js($text)" aria-live="polite">{{ $text }}</span>
</button>
