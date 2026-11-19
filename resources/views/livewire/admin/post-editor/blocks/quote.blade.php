{{-- Редагування блоку «quote» у конструкторі статей. Змінні: $i – номер блоку, $block – його дані. --}}
<x-bilingual model="blocks.{{ $i }}.data.text" type="textarea" :rows="2" placeholder="Цитата" />
<x-bilingual model="blocks.{{ $i }}.data.author" placeholder="Автор (необовʼязково)" compact translit optional />
