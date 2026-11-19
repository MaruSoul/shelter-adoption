<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SetLocale::class]);
        // На сервері перед сайтом стоїть nginx з HTTPS (docker/host-nginx): довіряємо його заголовкам
        // X-Forwarded-*, щоб посилання будувалися з https://, а IP відвідувача була справжньою.
        // Порт контейнера web відкрито лише на 127.0.0.1, тож підробити ці заголовки ззовні не можна.
        $middleware->trustProxies(at: '*');
        // Мову читаємо з незашифрованої cookie, щоб її можна було встановити без сесії
        $middleware->encryptCookies(except: [SetLocale::COOKIE]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
