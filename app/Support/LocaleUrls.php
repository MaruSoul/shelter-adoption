<?php

namespace App\Support;

use App\Routing\LocalizedUrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Відповідники поточної сторінки іншою мовою: для перемикача UA/EN і тегів hreflang.
 */
class LocaleUrls
{
    public const LOCALES = ['uk', 'en'];

    /** Чи сторінка має дві мовні версії з окремими адресами. */
    public static function isLocalized(?Route $route): bool
    {
        return $route !== null && in_array($route->getAction('localized'), self::LOCALES, true);
    }

    /** Мова, закладена в адресу сторінки, або null для сторінок без мовних версій. */
    public static function localeOf(?Route $route): ?string
    {
        return self::isLocalized($route) ? $route->getAction('localized') : null;
    }

    /**
     * Адреса тієї самої сторінки заданою мовою (з тими самими фільтрами в адресі).
     * Для сторінок без мовних версій – null.
     */
    public static function alternate(string $locale, ?Request $request = null): ?string
    {
        $request ??= request();
        $route = $request->route();
        if (! $route instanceof Route || ! self::isLocalized($route)) {
            return null;
        }

        /** @var LocalizedUrlGenerator $url */
        $url = app('url');
        $address = $url->routeIn($locale, $route->getName(), $route->parameters());
        $query = $request->getQueryString();

        return $query ? $address.'?'.$query : $address;
    }

    /** Знаходить маршрут за повною адресою сторінки сайту (наприклад, з Referer). */
    public static function routeForUrl(string $address): ?Route
    {
        if ($address === '' || ! str_starts_with($address, url('/'))) {
            return null;
        }
        try {
            $probe = Request::create($address);
            $route = app('router')->getRoutes()->match($probe);
            $route->bind($probe);

            return $route;
        } catch (\Throwable) {
            return null;
        }
    }
}
