@props(['network'])
{{-- Спрощені власні піктограми соцмереж (без сторонніх наборів іконок) --}}
<svg viewBox="0 0 24 24" aria-hidden="true" {{ $attributes }}>
    @switch($network)
        @case('instagram')
            <rect x="3" y="3" width="18" height="18" rx="5.5" fill="none" stroke="currentColor" stroke-width="2"/>
            <circle cx="12" cy="12" r="4.2" fill="none" stroke="currentColor" stroke-width="2"/>
            <circle cx="17.3" cy="6.7" r="1.3" fill="currentColor"/>
            @break
        @case('facebook')
            <path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.6-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8v3h2.5V21z" fill="currentColor"/>
            @break
        @case('telegram')
            <path d="M20.7 4.3 2.9 11.2c-1 .4-1 1.2 0 1.5l4.4 1.4 1.7 5.2c.2.6.5.7 1 .7.4 0 .6-.2.9-.5l2.2-2.1 4.6 3.4c.8.5 1.5.2 1.7-.8l3-14.2c.3-1.3-.5-1.9-1.7-1.5z" fill="currentColor"/>
            <path d="m8.2 14.2 9-5.7-6.6 6.6-.3 3.2" fill="none" stroke="#F6C945" stroke-width="1.3" stroke-linejoin="round"/>
            @break
        @case('tiktok')
            <path d="M15.6 3c.3 2.3 1.7 3.8 4.1 4v3c-1.5.1-2.9-.4-4.1-1.2v6.1a5.6 5.6 0 1 1-5.6-5.6c.3 0 .6 0 .9.1v3.1a2.6 2.6 0 1 0 1.7 2.4V3z" fill="currentColor"/>
            @break
        @case('youtube')
            <rect x="2" y="5" width="20" height="14" rx="4.5" fill="currentColor"/>
            <path d="M10 9v6l5.2-3z" fill="#F6C945"/>
            @break
    @endswitch
</svg>
