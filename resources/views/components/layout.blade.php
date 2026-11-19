@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'robots' => null,
    'alternates' => true,
])
@php
    $shelter = \App\Models\ShelterSetting::current();
    $brand = $shelter->tr('name');
    $tagline = $shelter->tr('tagline');
    $pageTitle = $title ? $title.' – '.$brand : $brand.' – '.($tagline ?? __('притулок для тварин'));
    $pageDescription = $description ?? __('Знайдіть друга в притулку «:name»: каталог котів і собак, які шукають дім, та анкета на прилаштування без реєстрації.', ['name' => $brand]);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($robots)<meta name="robots" content="{{ $robots }}">@endif
    {{-- Мовні версії сторінки для пошуковиків: українська – основна (x-default) --}}
    @if ($alternates && ($ukUrl = \App\Support\LocaleUrls::alternate('uk')))
        @php($ukCanonical = strtok($ukUrl, '?'))
        @php($enCanonical = strtok(\App\Support\LocaleUrls::alternate('en'), '?'))
        <link rel="alternate" hreflang="uk" href="{{ $ukCanonical }}">
        <link rel="alternate" hreflang="en" href="{{ $enCanonical }}">
        <link rel="alternate" hreflang="x-default" href="{{ $ukCanonical }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'uk_UA' }}">
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    {{-- Без власного зображення сторінки соцмережі показують логотип притулку --}}
    <meta property="og:image" content="{{ url($image ?? '/og-default.jpg') }}">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="favorites" content="{{ json_encode(auth()->user()->favorites()->pluck('animals.id')) }}" data-sync="{{ route('favorites.sync') }}" data-toggle="{{ url('/obrani') }}">
    @endauth
    {{ $head ?? '' }}
    <script>
        // М'яка поява блоків під час скролу: лише якщо людина не просила зменшити рух
        if (matchMedia('(prefers-reduced-motion: no-preference)').matches) {
            document.documentElement.classList.add('reveal');
            // Запобіжник: якщо скрипт сайту не завантажився, показуємо все
            setTimeout(() => window.__revealReady || document.documentElement.classList.remove('reveal'), 2500);
        }
    </script>
    {{-- Основні шрифти (кирилиця й латиниця з цифрами) завантажуємо одразу, щоб текст не «стрибав» після їх появи (CLS) --}}
    @if (is_file(public_path('build/manifest.json')))
        @foreach (['inter-cyrillic-400', 'inter-cyrillic-500', 'inter-cyrillic-600', 'inter-latin-400', 'inter-latin-500', 'inter-latin-600', 'nunito-cyrillic-800', 'nunito-latin-800'] as $font)
            <link rel="preload" as="font" type="font/woff2" href="{{ \Illuminate\Support\Facades\Vite::asset('node_modules/@fontsource/'.strtok($font, '-').'/files/'.$font.'-normal.woff2') }}" crossorigin>
        @endforeach
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 btn-primary">{{ __('Перейти до змісту') }}</a>

<header class="border-b border-line bg-bg" x-data="{ open: false }">
    <div class="mx-auto max-w-6xl px-6 py-4 flex flex-wrap items-center justify-between gap-x-2 gap-y-4 sm:gap-4">
        {{-- Логотип: велика лапка, назва капсом і підпис притулку одним блоком --}}
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 sm:gap-3 group" aria-label="{{ $brand }} – {{ __('на головну') }}">
            <img src="/logo-mark.svg" alt="" width="40" height="50" class="h-11 sm:h-14 w-auto shrink-0">
            <span class="flex flex-col leading-tight">
                {{-- На вузьких телефонах назва дрібніша, щоб поруч умістилася кнопка меню --}}
                <span class="font-display font-extrabold text-xl sm:text-2xl uppercase tracking-wide sm:tracking-wider text-primary whitespace-nowrap">{{ $brand }}</span>
                @if ($tagline)
                    <span class="hidden sm:block lg:hidden xl:block text-sm text-muted">{{ $tagline }}</span>
                @endif
            </span>
        </a>

        <button type="button" class="lg:hidden btn-ghost px-3 shrink-0" x-on:click="open = !open" x-bind:aria-expanded="open" aria-controls="main-nav" aria-label="{{ __('Меню') }}">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        <nav id="main-nav" aria-label="{{ __('Головне меню') }}"
             class="w-full lg:w-auto hidden lg:flex flex-col lg:flex-row gap-1 lg:gap-5 xl:gap-6 font-medium"
             x-bind:class="open && 'max-lg:flex'">
            <a href="{{ route('animals.index') }}" @class(['py-2', 'text-primary font-semibold' => request()->routeIs('animals.*', 'en.animals.*')])>{{ __('Знайти друга') }}</a>
            <a href="{{ route('blog.index') }}" @class(['py-2', 'text-primary font-semibold' => request()->routeIs('blog.*', 'en.blog.*')])>{{ __('Блог') }}</a>
            <a href="{{ route('home') }}#help" class="py-2">{{ __('Як допомогти') }}</a>
            <div class="flex flex-wrap gap-2 pt-2 lg:hidden">
                @include('components.partials.auth-links')
            </div>
        </nav>

        <div class="hidden lg:flex items-center gap-2">
            @include('components.partials.auth-links')
        </div>
    </div>
</header>

@if (session('status') && session('status') !== 'verification-link-sent')
    <div role="status" class="mx-auto max-w-6xl w-full px-6 pt-6">
        @if (session('celebrate'))
            <div class="relative rounded-2xl bg-success-soft text-success px-5 py-3 font-medium flex items-center gap-4">
                <span class="relative size-16 shrink-0"><x-confetti :count="14" /><x-mascot.cheer class="lapa-pop relative size-full" label="{{ __('Лапа радіє') }}" /></span>
                <span>{{ session('status') }}</span>
            </div>
        @else
            <div class="rounded-2xl bg-success-soft text-success px-5 py-3 font-medium">{{ session('status') }}</div>
        @endif
    </div>
@endif

<main id="main" class="flex-1">
    {{ $slot }}
</main>

<footer class="bg-ink text-bg mt-16">
    <div class="mx-auto max-w-6xl px-6 py-12 flex flex-wrap justify-between gap-8">
        <div class="flex flex-col gap-2 max-w-sm">
            <span class="size-32 rounded-full bg-bg p-1 mb-2"><img src="/logo.png" alt="{{ __('Логотип притулку') }}" width="480" height="505" loading="lazy" class="size-full object-contain"></span>
            <span class="font-display font-extrabold text-xl uppercase tracking-wider">{{ $brand }}</span>
            @if ($tagline)<span class="opacity-85">{{ $tagline }}</span>@endif
            <span class="opacity-85">
                @if ($address = $shelter->tr('address')){{ $address }}<br>@endif
                @if ($shelter->phone)<a href="tel:{{ preg_replace('/\s+/', '', $shelter->phone) }}" class="hover:underline">{{ $shelter->phone }}</a>@endif
                @if ($shelter->email) · <a href="mailto:{{ $shelter->email }}" class="hover:underline">{{ $shelter->email }}</a>@endif
            </span>
        </div>
        <div class="flex flex-col gap-8">
            <nav aria-label="{{ __('Нижнє меню') }}" class="flex flex-wrap gap-6">
                <a href="{{ route('animals.index') }}" class="hover:underline">{{ __('Знайти друга') }}</a>
                <a href="{{ route('blog.index') }}" class="hover:underline">{{ __('Блог') }}</a>
                <a href="{{ route('privacy') }}" class="hover:underline">{{ __('Політика конфіденційності') }}</a>
            </nav>
            @if ($socials = $shelter->socialLinks())
                <div class="flex flex-col gap-3">
                    <span class="font-semibold">{{ __('Слідкуй за нами') }}</span>
                    <ul class="flex flex-wrap gap-3">
                        @foreach ($socials as $network => $link)
                            <li>
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" aria-label="{{ $link['label'] }} {{ __('притулку') }}"
                                   class="size-12 rounded-full bg-sun text-ink flex items-center justify-center transition-transform hover:-translate-y-0.5 hover:bg-card">
                                    <x-icon.social :network="$network" class="size-6" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</footer>

@livewireScripts
</body>
</html>
