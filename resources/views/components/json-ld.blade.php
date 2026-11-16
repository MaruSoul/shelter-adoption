@props(['data'])
{{-- Розмітка schema.org; JSON_HEX_TAG не дає тексту з «</script>» зламати сторінку --}}
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
