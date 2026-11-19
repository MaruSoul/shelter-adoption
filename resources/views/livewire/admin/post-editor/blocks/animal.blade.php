{{-- Редагування блоку «animal» у конструкторі статей. Змінні: $i – номер блоку, $block – його дані. --}}
<select wire:model="blocks.{{ $i }}.data.animal_id" class="field" aria-label="Тварина">
    <option value="">Оберіть тварину з каталогу</option>
    @foreach ($animals as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
</select>
<p class="text-sm text-muted">Картка тварини перекладається автоматично з її сторінки.</p>
