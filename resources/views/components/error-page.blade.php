@props(['code', 'title', 'text', 'mascot' => 'peek'])
{{--
    Спільний шаблон сторінок помилок (403, 404, 419, 429, 500, 503).
    Окрема легка сторінка без загального layout: помилка може статися без сесії, до проміжних обробників
    або коли недоступна база даних, тому назву притулку беремо обережно, з запасним варіантом.
--}}
@php
    try {
        $brand = \App\Models\ShelterSetting::current()->tr('name');
    } catch (\Throwable) {
        $brand = config('app.name');
    }
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} – {{ $brand }}</title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex flex-col">
    <header class="border-b border-line">
        <div class="mx-auto max-w-6xl px-6 py-4">
            <a href="{{ url(app()->getLocale() === 'en' ? '/en' : '/') }}" class="flex items-center gap-3" aria-label="{{ $brand }} – {{ __('на головну') }}">
                <img src="/logo-mark.svg" alt="" width="40" height="50" class="h-14 w-auto shrink-0">
                <span class="font-display font-extrabold text-2xl uppercase tracking-wider text-primary">{{ $brand }}</span>
            </a>
        </div>
    </header>
    <main class="flex-1 flex items-center justify-center px-6 py-16">
        <div class="flex flex-col items-center text-center max-w-lg">
            <x-dynamic-component :component="'mascot.'.$mascot" class="w-64 -mb-3 relative z-0" label="{{ __('Лапа визирає з-за таблички') }}" />
            <div class="card relative z-10 w-full p-8 flex flex-col items-center gap-3 shadow-xl shadow-primary/10">
                <span class="font-display font-extrabold text-6xl text-primary">{{ $code }}</span>
                <h1 class="text-2xl">{{ $title }}</h1>
                <p class="text-muted">{{ $text }}</p>
                <div class="flex flex-wrap justify-center gap-3 pt-2">
                    @if ($slot->isNotEmpty())
                        {{ $slot }}
                    @else
                        <a href="{{ url(app()->getLocale() === 'en' ? '/en' : '/') }}" class="btn-primary">{{ __('На головну') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>
