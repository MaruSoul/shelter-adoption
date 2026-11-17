<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Мова інтерфейсу: українська (типово) або англійська, вибір зберігається в cookie.
 * Панель адміністратора – внутрішній інструмент притулку, тому завжди українською.
 */
class SetLocale
{
    public const COOKIE = 'locale';

    public const SUPPORTED = ['uk', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(self::COOKIE);
        if (! in_array($locale, self::SUPPORTED, true) || $this->isAdminArea($request)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    private function isAdminArea(Request $request): bool
    {
        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        // Запити компонентів Livewire приходять на окрему адресу – орієнтуємося на сторінку, з якої їх надіслано
        $referer = (string) $request->headers->get('referer');

        return $request->is('livewire*/*') && preg_match('#^https?://[^/]+/admin(/|$|\?)#', $referer) === 1;
    }
}
