<?php

namespace App\Support;

/** Нормалізація українського номера до вигляду +380XXXXXXXXX. */
final class Phone
{
    public const PATTERN = '/^\+380\d{9}$/';

    public static function normalize(string $raw): string
    {
        // Прибираємо пробіли, дужки й дефіси, які люди часто вводять у номері
        $digits = preg_replace('/\D+/', '', $raw);
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '38'.$digits;
        }

        return '+'.$digits;
    }
}
