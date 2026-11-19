@props(['seo'])
{{-- Текст для пошуку внизу сторінки (редагується в адмінці: «SEO»). Порожній – блоку немає --}}
@if ($text = $seo->tr('text'))
    <section {{ $attributes->merge(['class' => 'mx-auto max-w-4xl w-full px-6 py-12']) }} @unless ($seo->isShownInLocale('text')) lang="{{ app()->getLocale() === 'en' ? 'uk' : 'en' }}" @endunless>
        <div class="seo-text prose-links">{{ \App\Support\SimpleMarkup::block($text) }}</div>
    </section>
@endif
