@props([
    'label' => null,
    'model',
    'enModel' => null,
    'live' => false,
    'type' => 'text',
    'rows' => 3,
    'hint' => null,
    'required' => false,
    'placeholder' => null,
    'enPlaceholder' => null,
    'translit' => false,
    'optional' => false,
    'compact' => false,
    'inputClass' => '',
])
{{--
    Двомовне поле: українська (основна) і англійська пара поруч, на телефоні – одна під одною.
    Значення англійською береться з властивості з тим самим іменем і суфіксом _en.
    Порожній переклад при заповненій українській підсвічується (див. .bi-missing в app.css),
    а <x-translation-meter> рахує такі поля на всій сторінці.
--}}
@php
    $enModel ??= $model.'_en';
    $ukWire = $live ? 'wire:model.live.blur' : 'wire:model';
    $id = 'bi-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $model));
    $fieldClass = 'field font-normal '.($type === 'textarea' ? 'py-3 resize-y ' : '').($compact ? 'pl-12 ' : '').$inputClass;
    $ukLabel = trim(($label ?? $placeholder ?? '').' – '.__('українською'), ' –');
    $enLabel = trim(($label ?? $placeholder ?? '').' – '.__('англійською'), ' –');
@endphp
<div data-bi @if ($optional) data-bi-optional @endif {{ $attributes->class('flex flex-col gap-2') }}>
    @if ($label)
        <div class="flex flex-wrap items-baseline gap-x-2 font-semibold">
            <span>{{ $label }}@if ($required) *@endif</span>
            @if ($hint)<span class="font-normal text-sm text-muted">{{ $hint }}</span>@endif
        </div>
    @endif

    <div class="grid gap-3 md:grid-cols-2">
        <div class="flex flex-col gap-1.5">
            @unless ($compact)
                <label for="{{ $id }}-uk" class="bi-tag"><span class="bi-flag">UA</span>{{ __('Українською') }}</label>
            @endunless
            <div class="relative">
                @if ($compact)<span class="bi-pill" aria-hidden="true">UA</span>@endif
                @if ($type === 'textarea')
                    <textarea id="{{ $id }}-uk" {{ $ukWire }}="{{ $model }}" rows="{{ $rows }}" placeholder="{{ $placeholder ?? ' ' }}" aria-label="{{ $ukLabel }}" class="bi-uk {{ $fieldClass }}"></textarea>
                @else
                    <input id="{{ $id }}-uk" type="text" {{ $ukWire }}="{{ $model }}" placeholder="{{ $placeholder ?? ' ' }}" aria-label="{{ $ukLabel }}" class="bi-uk {{ $fieldClass }}">
                @endif
            </div>
            @error($model)<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            @unless ($compact)
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label for="{{ $id }}-en" class="bi-tag"><span class="bi-flag bi-flag-en">EN</span>{{ __('Англійською') }}</label>
                    <span class="flex items-center gap-3">
                        <span class="bi-missing">{{ __('немає перекладу') }}</span>
                        @if ($translit)
                            <button type="button" wire:click="transliterate('{{ $model }}')" class="text-sm font-semibold text-primary hover:underline">{{ __('Транслітерувати') }}</button>
                        @endif
                    </span>
                </div>
            @endunless
            <div class="relative">
                @if ($compact)<span class="bi-pill bi-flag-en" aria-hidden="true">EN</span>@endif
                @if ($type === 'textarea')
                    <textarea id="{{ $id }}-en" wire:model="{{ $enModel }}" rows="{{ $rows }}" lang="en" placeholder="{{ $enPlaceholder ?? __('Переклад англійською') }}" aria-label="{{ $enLabel }}" class="bi-en {{ $fieldClass }}"></textarea>
                @else
                    <input id="{{ $id }}-en" type="text" wire:model="{{ $enModel }}" lang="en" placeholder="{{ $enPlaceholder ?? __('Переклад англійською') }}" aria-label="{{ $enLabel }}" class="bi-en {{ $fieldClass }}">
                @endif
                @if ($compact && $translit)
                    <button type="button" wire:click="transliterate('{{ $model }}')" class="absolute right-2 top-2.5 text-xs font-semibold text-primary bg-card/90 rounded-full px-2 py-1 hover:underline">{{ __('Транслітерувати') }}</button>
                @endif
            </div>
            @error($enModel)<span class="error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
