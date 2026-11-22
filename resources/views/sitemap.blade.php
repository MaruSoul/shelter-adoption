<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($pages as $page)
@foreach (array_filter(['uk' => $page['uk'], 'en' => $page['en']]) as $loc)
    <url>
        <loc>{{ $loc }}</loc>
        <lastmod>{{ $page['lastmod']->toAtomString() }}</lastmod>
@if ($page['en'])
        <xhtml:link rel="alternate" hreflang="uk" href="{{ $page['uk'] }}"/>
        <xhtml:link rel="alternate" hreflang="en" href="{{ $page['en'] }}"/>
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $page['uk'] }}"/>
@endif
    </url>
@endforeach
@endforeach
</urlset>
