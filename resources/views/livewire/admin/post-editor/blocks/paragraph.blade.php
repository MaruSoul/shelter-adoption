{{-- Редагування блоку «paragraph» у конструкторі статей. Змінні: $i – номер блоку, $block – його дані. --}}
<x-bilingual model="blocks.{{ $i }}.data.text" type="textarea" :rows="5" placeholder="Текст абзацу" />
<p class="text-sm text-muted">{{ \App\Support\SimpleMarkup::HINT }}. Внутрішні посилання на тварин і статті допомагають сайту в пошуку.</p>
