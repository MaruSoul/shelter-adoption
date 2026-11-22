@php($account = $method->detail('account'))
@if (str_starts_with($account, 'https://'))
    <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
        <a href="{{ $account }}" target="_blank" rel="noopener" class="btn-sun min-h-14 px-8 text-lg">{{ __('Переказати через PayPal') }}</a>
        <span class="text-sm text-muted max-w-xs">{{ __('Зручно для допомоги з-за кордону.') }}</span>
    </div>
@else
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-line px-4 py-3">
            <div class="flex flex-col min-w-0">
                <span class="text-sm text-muted">{{ __('Адреса PayPal') }}</span>
                <span class="font-semibold break-all">{{ $account }}</span>
            </div>
            <x-copy-button :value="$account" label="PayPal" icon-only-on-mobile />
        </div>
        <p class="text-sm text-muted">{{ __('У PayPal оберіть «Надіслати» й вставте цю адресу. Зручно для допомоги з-за кордону.') }}</p>
    </div>
@endif
