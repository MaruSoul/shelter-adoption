<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Український IBAN: UA + 27 цифр, контрольна сума за ISO 13616 (залишок 97).
 * Пробіли допускаються – їх часто копіюють разом із номером.
 */
class Iban implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid((string) $value)) {
            $fail('Перевірте IBAN: він починається з UA і має 29 символів (UA і 27 цифр). Ймовірно, у номері помилка.');
        }
    }

    public static function normalize(string $iban): string
    {
        return strtoupper(preg_replace('/\s+/', '', $iban));
    }

    /** UA12 3456 7890 … – групами по 4 символи, як у банківських застосунках. */
    public static function format(string $iban): string
    {
        return trim(chunk_split(self::normalize($iban), 4, ' '));
    }

    public static function isValid(string $iban): bool
    {
        $iban = self::normalize($iban);
        if (! preg_match('/^UA\d{27}$/', $iban)) {
            return false;
        }

        // Перші 4 символи – у кінець, літери – числами (A = 10 … Z = 35), і залишок від ділення на 97 має бути 1
        $digits = '';
        foreach (str_split(substr($iban, 4).substr($iban, 0, 4)) as $char) {
            $digits .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }
        $remainder = 0;
        foreach (str_split($digits, 7) as $chunk) {
            $remainder = (int) (($remainder.$chunk) % 97);
        }

        return $remainder === 1;
    }
}
