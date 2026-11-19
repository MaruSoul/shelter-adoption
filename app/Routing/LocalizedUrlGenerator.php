<?php

namespace App\Routing;

use Illuminate\Routing\UrlGenerator;

/**
 * Генератор адрес із підтримкою англійської версії сайту.
 *
 * Публічні сторінки зареєстровано двічі: українська – без префікса (`animals.show` → /tvaryny/leo-3),
 * англійська – з префіксом /en і назвою `en.*` (`en.animals.show` → /en/tvaryny/leo-3).
 * Коли сайт показується англійською, звичайний виклик route('animals.show', …) сам повертає
 * англійську адресу, тож шаблони й компоненти не потрібно змінювати.
 */
class LocalizedUrlGenerator extends UrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        return parent::route($this->localizedName($name, app()->getLocale()), $parameters, $absolute);
    }

    /** Адреса сторінки заданою мовою незалежно від мови, якою показано поточну сторінку. */
    public function routeIn(string $locale, string $name, $parameters = [], bool $absolute = true): string
    {
        return parent::route($this->localizedName(self::baseName($name), $locale), $parameters, $absolute);
    }

    /** Назва маршруту без мовного префікса: en.animals.show → animals.show. */
    public static function baseName(string $name): string
    {
        return str_starts_with($name, 'en.') ? substr($name, 3) : $name;
    }

    private function localizedName(string $name, string $locale): string
    {
        if ($locale === 'en' && ! str_starts_with($name, 'en.') && $this->routes->hasNamedRoute('en.'.$name)) {
            return 'en.'.$name;
        }

        return $name;
    }
}
