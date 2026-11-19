<?php

namespace App\Models\Concerns;

/**
 * Назва запису довідника (вид, порода, забарвлення, рубрика) мовою відвідувача.
 *
 * Англійська назва зберігається в полі з суфіксом _en і редагується в панелі «Довідники».
 * Якщо її не заповнено, пробуємо файл перекладів lang/en.json, а далі – українську назву.
 */
trait HasLocalizedName
{
    public function label(string $field = 'name', ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $uk = (string) $this->getAttribute($field);

        if ($locale === 'en') {
            $en = $this->getAttribute($field.'_en');

            return filled($en) ? $en : __($uk, [], 'en');
        }

        return $uk;
    }
}
