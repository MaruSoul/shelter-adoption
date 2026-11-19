@props(['blocks'])
{{--
    Рендеринг блоків статті. Шаблон кожного типу – resources/views/blog/blocks/{тип}.blade.php.
    Увесь текст екранується; у абзацах і списках розпізнаються лише посилання й жирний (App\Support\SimpleMarkup).
--}}
<div class="flex flex-col gap-5 text-lg leading-relaxed prose-links">
    @foreach ($blocks as $block)
        {{-- Блок без перекладу показується українською: позначаємо мову для екранних читачів --}}
        @include('blog.blocks.'.$block['type'], ['block' => $block, 'lang' => isset($block['lang']) ? 'lang='.$block['lang'] : ''])
    @endforeach
</div>
