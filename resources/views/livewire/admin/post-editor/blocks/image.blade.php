{{-- Редагування блоку «image» у конструкторі статей. Змінні: $i – номер блоку, $block – його дані. --}}
@php($pending = $blockImages[$i] ?? null)
@if ($pending && method_exists($pending, 'isPreviewable') && $pending->isPreviewable())
    <img src="{{ $pending->temporaryUrl() }}" alt="" class="max-h-64 rounded-2xl self-start border-2 border-dashed border-primary/40">
@elseif (! empty($block['data']['path']))
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($block['data']['path']) }}" alt="" class="max-h-64 rounded-2xl self-start">
@endif
<span wire:loading wire:target="blockImages.{{ $i }}" class="text-sm text-muted">Завантажуємо…</span>
<label class="btn-outline cursor-pointer self-start">{{ empty($block['data']['path']) ? 'Обрати зображення' : 'Замінити' }}
    <input type="file" wire:model="blockImages.{{ $i }}" accept="image/jpeg,image/png,image/webp" class="sr-only">
</label>
@error('blockImages.'.$i)<span class="error">{{ $message }}</span>@enderror
<x-bilingual model="blocks.{{ $i }}.data.alt" placeholder="Опис зображення для незрячих *" compact />
<x-bilingual model="blocks.{{ $i }}.data.caption" placeholder="Підпис (необовʼязково)" compact />
