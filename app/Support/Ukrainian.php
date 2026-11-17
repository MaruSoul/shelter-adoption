<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Допоміжні функції для українських форм слів.
 */
final class Ukrainian
{
    /**
     * Узгодження іменника з числом: 1 рік, 2 роки, 5 років, 11 років, 21 рік.
     *
     * @param  array{0: string, 1: string, 2: string}  $forms
     */
    public static function plural(int $n, array $forms): string
    {
        $mod100 = $n % 100;
        $mod10 = $n % 10;

        if ($mod100 >= 11 && $mod100 <= 14) {
            return $forms[2];
        }

        return match (true) {
            $mod10 === 1 => $forms[0],
            $mod10 >= 2 && $mod10 <= 4 => $forms[1],
            default => $forms[2],
        };
    }

    /** «2 роки 3 місяці», «7 місяців», «менше місяця». */
    public static function age(CarbonInterface $birthDate, ?CarbonInterface $now = null): string
    {
        $now ??= now();
        $diff = $birthDate->diff($now);
        $years = $diff->y;
        $months = $diff->m;

        $parts = [];
        $english = app()->getLocale() === 'en';
        if ($years > 0) {
            $parts[] = $years.' '.($english ? ($years === 1 ? 'year' : 'years') : self::plural($years, ['рік', 'роки', 'років']));
        }
        if ($months > 0) {
            $parts[] = $months.' '.($english ? ($months === 1 ? 'month' : 'months') : self::plural($months, ['місяць', 'місяці', 'місяців']));
        }

        return $parts === [] ? ($english ? 'under a month' : 'менше місяця') : implode(' ', $parts);
    }

    /**
     * Назва населеного пункту для поточної мови: в англійській версії – латиницею
     * за офіційною транслітерацією (постанова КМУ № 55 від 27.01.2010).
     */
    public static function place(string $name): string
    {
        return app()->getLocale() === 'en' ? self::transliterate($name) : $name;
    }

    public static function transliterate(string $text): string
    {
        // Літери, що на початку слова передаються інакше, ніж усередині
        $initial = ['Є' => 'Ye', 'Ї' => 'Yi', 'Й' => 'Y', 'Ю' => 'Yu', 'Я' => 'Ya', 'є' => 'ye', 'ї' => 'yi', 'й' => 'y', 'ю' => 'yu', 'я' => 'ya'];
        $map = [
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'H', 'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Є' => 'Ie', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'Y', 'І' => 'I', 'Ї' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
            'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
            'Ь' => '', 'Ю' => 'Iu', 'Я' => 'Ia',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'є' => 'ie', 'ж' => 'zh', 'з' => 'z',
            'и' => 'y', 'і' => 'i', 'ї' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
            'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
            'ь' => '', 'ю' => 'iu', 'я' => 'ia', 'ʼ' => '', '’' => '', "'" => '',
        ];
        $text = str_replace(['Зг', 'зг'], ['Zgh', 'zgh'], $text);

        $text = preg_replace_callback('/(?<![\\p{L}ʼ’\'])[ЄЇЙЮЯєїйюя]/u', fn (array $m) => $initial[$m[0]], $text);

        return strtr($text, $map);
    }
}
