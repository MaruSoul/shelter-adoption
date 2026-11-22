<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Посилання на банку Monobank: https://send.monobank.ua/jar/XXXXXXXX */
class MonobankJarUrl implements ValidationRule
{
    public const PATTERN = '~^https://send\.monobank\.ua/jar/[A-Za-z0-9]{6,20}/?(?:\?.*)?$~';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match(self::PATTERN, trim((string) $value))) {
            $fail('Вставте посилання на банку з застосунку Monobank: «Банка» → «Поділитися» – воно має вигляд https://send.monobank.ua/jar/…');
        }
    }
}
