{{-- Редагування блоку «list» у конструкторі статей. Змінні: $i – номер блоку, $block – його дані. --}}
<select wire:model="blocks.{{ $i }}.data.style" class="field w-auto self-start" aria-label="Вигляд списку"><option value="bullet">Маркований</option><option value="ordered">Нумерований</option></select>
<div class="hidden md:grid grid-cols-2 gap-3 pr-12 text-sm font-semibold text-muted" aria-hidden="true">
    <span class="bi-tag"><span class="bi-flag">UA</span>Українською</span>
    <span class="bi-tag"><span class="bi-flag bi-flag-en">EN</span>Англійською</span>
</div>
@foreach ($block['data']['items'] as $j => $item)
    <div class="flex gap-2 items-start" wire:key="b{{ $i }}-item-{{ $j }}">
        <x-bilingual model="blocks.{{ $i }}.data.items.{{ $j }}" en-model="blocks.{{ $i }}.data.items_en.{{ $j }}" placeholder="Пункт {{ $j + 1 }}" compact class="grow" />
        <button type="button" wire:click="removeListItem({{ $i }}, {{ $j }})" class="btn-ghost px-3 text-danger" aria-label="Видалити пункт {{ $j + 1 }}">×</button>
    </div>
@endforeach
<button type="button" wire:click="addListItem({{ $i }})" class="btn-ghost self-start">+ Пункт</button>
