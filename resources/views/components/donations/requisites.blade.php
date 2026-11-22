@php
    $rows = array_filter([
        __('Отримувач') => $method->detail('recipient'),
        'IBAN' => $method->detail('iban') ? \App\Rules\Iban::format($method->detail('iban')) : null,
        __('ЄДРПОУ / ІПН') => $method->detail('code'),
        __('Банк') => $method->detail('bank'),
        __('Призначення платежу') => $method->detail('purpose'),
    ]);
    $all = collect($rows)->map(fn ($value, $label) => $label.': '.$value)->implode("\n");
@endphp
{{-- Реквізити з кнопкою «Копіювати» біля кожного рядка: переписувати IBAN з телефона незручно --}}
<dl class="flex flex-col divide-y divide-line rounded-2xl border border-line">
    @foreach ($rows as $label => $value)
        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <div class="flex flex-col min-w-0">
                <dt class="text-sm text-muted">{{ $label }}</dt>
                <dd class="font-semibold {{ $label === 'IBAN' ? 'font-mono tracking-wide break-all' : 'break-words' }}" @if ($label === __('Призначення платежу') && app()->getLocale() === 'en') lang="uk" @endif>{{ $value }}</dd>
            </div>
            <x-copy-button :value="$label === 'IBAN' ? $method->detail('iban') : $value" :label="$label" icon-only-on-mobile />
        </div>
    @endforeach
</dl>
<div class="flex flex-wrap items-center gap-3">
    <x-copy-button :value="$all" :label="__('усі реквізити')" :text="__('Копіювати всі реквізити')" class="!min-h-12 !px-5 !text-base" />
    <span class="text-sm text-muted">{{ __('Скопіюйте всі реквізити одним натиском і вставте в застосунок свого банку.') }}</span>
</div>
