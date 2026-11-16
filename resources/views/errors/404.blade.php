@php($shelter = \App\Models\ShelterSetting::current())
{{-- Окрема легка сторінка: 404 часто віддається без сесії, тому не використовуємо загальний layout з кабінетом --}}
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Сторінку не знайдено – {{ $shelter->name }}</title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex flex-col">
    <header class="border-b border-line">
        <div class="mx-auto max-w-6xl px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ $shelter->name }} – на головну">
                <x-icon.paw class="size-12 shrink-0" />
                <span class="font-display font-extrabold text-2xl uppercase tracking-wider text-primary">{{ $shelter->name }}</span>
            </a>
        </div>
    </header>
    <main class="flex-1 flex items-center justify-center px-6 py-16">
        <div class="flex flex-col items-center text-center max-w-lg">
            <x-mascot.peek class="w-64 -mb-3 relative z-0" label="Лапа визирає з-за таблички" />
            <div class="card relative z-10 w-full p-8 flex flex-col items-center gap-3 shadow-xl shadow-primary/10">
                <span class="font-display font-extrabold text-6xl text-primary">404</span>
                <h1 class="text-2xl">Такої сторінки немає</h1>
                <p class="text-muted">Можливо, тварина вже знайшла дім і її картку прибрали, або посилання застаріло. Лапа пропонує пошукати інших друзів.</p>
                <div class="flex flex-wrap justify-center gap-3 pt-2">
                    <a href="{{ route('animals.index') }}" class="btn-primary">Знайти друга</a>
                    <a href="{{ route('home') }}" class="btn-outline">На головну</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
