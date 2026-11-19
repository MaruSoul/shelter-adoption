{{-- Редагування блоку «heading» у конструкторі статей. Змінні: $i – номер блоку, $block – його дані. --}}
<x-bilingual model="blocks.{{ $i }}.data.text" placeholder="Текст заголовка" input-class="font-display font-bold text-xl" />
<select wire:model="blocks.{{ $i }}.data.level" class="field w-auto self-start" aria-label="Рівень заголовка"><option value="2">Розділ</option><option value="3">Підрозділ</option></select>
