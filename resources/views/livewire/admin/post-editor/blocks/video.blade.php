{{-- Редагування блоку «video» у конструкторі статей. Змінні: $i – номер блоку, $block – його дані. --}}
<p class="text-sm text-muted">Скопіюйте адресу відео з YouTube (звичайне відео або Shorts) чи Vimeo – вставку сайт зробить сам.
    Англійське посилання необовʼязкове: якщо його немає, англійська версія статті покаже те саме відео.</p>
<div class="grid gap-3 md:grid-cols-2">
    @foreach (['url' => ['UA', 'Посилання на відео – українська версія', ''], 'url_en' => ['EN', 'Посилання на відео – англійська версія', 'bi-flag-en']] as $field => [$flag, $label, $flagClass])
        <div class="flex flex-col gap-2">
            <div class="relative">
                <span class="bi-pill {{ $flagClass }}" aria-hidden="true">{{ $flag }}</span>
                <input type="url" wire:model.live.blur="blocks.{{ $i }}.data.{{ $field }}" class="field pl-12" inputmode="url"
                       placeholder="{{ $field === 'url' ? 'https://www.youtube.com/watch?v=…' : 'Те саме відео, що й українською' }}" aria-label="{{ $label }}"
                       @if ($field === 'url_en') lang="en" @endif>
            </div>
            @error('blocks.'.$i.'.data.'.$field)<span class="error">{{ $message }}</span>@enderror
            @if (($video = \App\Services\Blocks\VideoBlock::parse($block['data'][$field] ?? '')) && $video['thumb'])
                <img src="{{ $video['thumb'] }}" alt="" class="max-h-32 rounded-2xl self-start">
            @endif
        </div>
    @endforeach
</div>
<x-bilingual model="blocks.{{ $i }}.data.caption" placeholder="Підпис (необовʼязково)" compact />
