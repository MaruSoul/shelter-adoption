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
        if ($years > 0) {
            $parts[] = $years.' '.self::plural($years, ['рік', 'роки', 'років']);
        }
        if ($months > 0) {
            $parts[] = $months.' '.self::plural($months, ['місяць', 'місяці', 'місяців']);
        }

        return $parts === [] ? 'менше місяця' : implode(' ', $parts);
    }
}
