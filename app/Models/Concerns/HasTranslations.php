<?php

namespace App\Models\Concerns;

/**
 * Двомовні текстові поля: українське значення в полі `x`, англійське – у `x_en`.
 *
 * Модель оголошує перелік полів константою TRANSLATABLE. На сайті показується значення
 * мовою відвідувача; якщо перекладу немає, – текст іншою мовою (зазвичай український),
 * щоб сторінка ніколи не лишалася порожньою.
 */
trait HasTranslations
{
    /** Значення поля мовою інтерфейсу з запасним варіантом. */
    public function tr(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $uk = $this->getAttribute($field);
        $en = $this->getAttribute($field.'_en');

        if ($locale === 'en') {
            return filled($en) ? $en : $uk;
        }

        return filled($uk) ? $uk : $en;
    }

    /** Чи показується поле мовою інтерфейсу (а не запасною). */
    public function isShownInLocale(string $field, ?string $locale = null): bool
    {
        $locale ??= app()->getLocale();
        $value = $this->getAttribute($locale === 'en' ? $field.'_en' : $field);

        return filled($value) || blank($this->tr($field, $locale));
    }

    /** Поля, у яких є український текст, але немає англійського. */
    public function missingTranslations(): array
    {
        return array_values(array_filter(
            static::TRANSLATABLE,
            fn (string $f) => filled($this->getAttribute($f)) && blank($this->getAttribute($f.'_en')),
        ));
    }
}
