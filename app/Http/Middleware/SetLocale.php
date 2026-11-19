<?php

namespace App\Http\Middleware;

use App\Support\LocaleUrls;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Мова інтерфейсу: українська (типово) або англійська.
 *
 * Публічні сторінки мають окремі адреси для кожної мови (/tvaryny і /en/tvaryny) – мову визначає адреса,
 * а останній вибір запамʼятовується в cookie. Сторінки без мовних адрес (кабінет, вхід) беруть мову з cookie.
 * Запити Livewire і обраних тварин орієнтуються на сторінку, з якої їх надіслано (Referer).
 * Панель адміністратора – внутрішній інструмент притулку, тому завжди українською.
 */
class SetLocale
{
    public const COOKIE = 'locale';

    public const SUPPORTED = ['uk', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $fromAddress = LocaleUrls::localeOf($request->route());
        $locale = $fromAddress ?? $this->fromReferer($request) ?? $request->cookie(self::COOKIE);

        if (! in_array($locale, self::SUPPORTED, true) || $this->isAdminArea($request)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        $response = $next($request);

        // Відвідувач відкрив сторінку певною мовою – кабінет і вхід далі показуються нею ж
        if ($fromAddress !== null && $request->cookie(self::COOKIE) !== $fromAddress && method_exists($response, 'withCookie')) {
            $response->withCookie(cookie()->forever(self::COOKIE, $fromAddress, httpOnly: false));
        }

        return $response;
    }

    /** Мова сторінки, з якої надіслано службовий запит (Livewire, список обраних). */
    private function fromReferer(Request $request): ?string
    {
        if (! $request->is('livewire*', 'livewire*/*', 'obrani/*')) {
            return null;
        }

        return LocaleUrls::localeOf(LocaleUrls::routeForUrl((string) $request->headers->get('referer')));
    }

    private function isAdminArea(Request $request): bool
    {
        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        $referer = (string) $request->headers->get('referer');

        return $request->is('livewire*', 'livewire*/*') && preg_match('#^https?://[^/]+/admin(/|$|\?)#', $referer) === 1;
    }
}
