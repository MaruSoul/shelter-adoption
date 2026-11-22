<div class="flex flex-wrap items-center gap-x-6 gap-y-3">
    <a href="{{ $method->detail('url') }}" target="_blank" rel="noopener" class="btn-sun min-h-14 px-8 text-lg">
        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4h10M8 4v2a4 4 0 0 1-2 3.5V18a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9.5A4 4 0 0 1 16 6V4"/><path d="M12 11v5M9.5 13.5h5"/></svg>
        {{ __('Поповнити банку') }}
    </a>
    <span class="text-sm text-muted max-w-xs">{{ __('Карткою будь-якого банку, Apple Pay чи Google Pay – реєстрація не потрібна.') }}</span>
</div>
