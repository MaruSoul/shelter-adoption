{{--
    Мова сторінки помилки. Для адреси без маршруту (404) мовний middleware не спрацьовує,
    тому визначаємо мову тут – до того, як шаблон помилки перекладе заголовок і текст.
--}}
@php
    if (! request()->route()) {
        $cookie = request()->cookie(\App\Http\Middleware\SetLocale::COOKIE);
        app()->setLocale(request()->is('en', 'en/*') ? 'en' : (in_array($cookie, \App\Http\Middleware\SetLocale::SUPPORTED, true) ? $cookie : config('app.locale')));
    }
@endphp
