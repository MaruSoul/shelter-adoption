@props([
    'title' => null,
    'description' => null,
    'image' => null,
])
@php
    $shelter = \App\Models\ShelterSetting::current();
    $pageTitle = $title ? $title.' – '.$shelter->name : $shelter->name.' – '.($shelter->tagline ?? 'притулок для тварин');
    $pageDescription = $description ?? 'Знайдіть друга в притулку «'.$shelter->name.'»: каталог котів і собак, які шукають дім, та анкета на прилаштування без реєстрації.';
@endphp
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $shelter->name }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($image)
        <meta property="og:image" content="{{ url($image) }}">
    @endif
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 btn-primary">Перейти до змісту</a>

<header class="border-b border-line bg-bg" x-data="{ open: false }">
    <div class="mx-auto max-w-6xl px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        {{-- Логотип: велика лапка, назва капсом і підпис притулку одним блоком --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3 group" aria-label="{{ $shelter->name }} – на головну">
            <x-icon.paw class="size-12 shrink-0" />
            <span class="flex flex-col leading-tight">
                <span class="font-display font-extrabold text-2xl uppercase tracking-wider text-primary">{{ $shelter->name }}</span>
                @if ($shelter->tagline)
                    <span class="hidden sm:block text-sm text-muted">{{ $shelter->tagline }}</span>
                @endif
            </span>
        </a>

        <button type="button" class="md:hidden btn-ghost px-3" x-on:click="open = !open" x-bind:aria-expanded="open" aria-controls="main-nav" aria-label="Меню">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        <nav id="main-nav" aria-label="Головне меню"
             class="w-full md:w-auto md:flex flex-col md:flex-row gap-1 md:gap-6 font-medium"
             x-bind:class="open ? 'flex' : 'hidden md:flex'">
            <a href="{{ route('animals.index') }}" @class(['py-2', 'text-primary font-semibold' => request()->routeIs('animals.*') && ! request('species')])>Знайти друга</a>
            <a href="{{ route('animals.index', ['species' => 'cat']) }}" @class(['py-2', 'text-primary font-semibold' => request('species') === 'cat'])>Котики</a>
            <a href="{{ route('animals.index', ['species' => 'dog']) }}" @class(['py-2', 'text-primary font-semibold' => request('species') === 'dog'])>Песики</a>
            <a href="{{ route('blog.index') }}" @class(['py-2', 'text-primary font-semibold' => request()->routeIs('blog.*')])>Блог</a>
            <a href="{{ route('home') }}#help" class="py-2">Як допомогти</a>
            <div class="flex flex-wrap gap-2 pt-2 md:hidden">
                @include('components.partials.auth-links')
            </div>
        </nav>

        <div class="hidden md:flex items-center gap-2">
            @include('components.partials.auth-links')
        </div>
    </div>
</header>

@if (session('status') && session('status') !== 'verification-link-sent')
    <div role="status" class="mx-auto max-w-6xl w-full px-6 pt-6">
        @if (session('celebrate'))
            <div class="relative rounded-2xl bg-success-soft text-success px-5 py-3 font-medium flex items-center gap-4">
                <span class="relative size-16 shrink-0"><x-confetti :count="14" /><x-mascot.cheer class="lapa-pop relative size-full" label="Лапа радіє" /></span>
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
            <span class="font-display font-extrabold text-xl uppercase tracking-wider">{{ $shelter->name }}</span>
            @if ($shelter->tagline)<span class="opacity-85">{{ $shelter->tagline }}</span>@endif
            <span class="opacity-85">
                @if ($shelter->address){{ $shelter->address }}<br>@endif
                @if ($shelter->phone)<a href="tel:{{ preg_replace('/\s+/', '', $shelter->phone) }}" class="hover:underline">{{ $shelter->phone }}</a>@endif
                @if ($shelter->email) · <a href="mailto:{{ $shelter->email }}" class="hover:underline">{{ $shelter->email }}</a>@endif
            </span>
        </div>
        <div class="flex flex-col gap-8">
            <nav aria-label="Нижнє меню" class="flex flex-wrap gap-6">
                <a href="{{ route('animals.index') }}" class="hover:underline">Знайти друга</a>
                <a href="{{ route('blog.index') }}" class="hover:underline">Блог</a>
                <a href="{{ route('privacy') }}" class="hover:underline">Політика конфіденційності</a>
            </nav>
            @if ($socials = $shelter->socialLinks())
                <div class="flex flex-col gap-3">
                    <span class="font-semibold">Слідкуй за нами</span>
                    <ul class="flex flex-wrap gap-3">
                        @foreach ($socials as $network => $link)
                            <li>
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" aria-label="{{ $link['label'] }} притулку"
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
