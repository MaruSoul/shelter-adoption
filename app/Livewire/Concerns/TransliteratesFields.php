<?php

namespace App\Livewire\Concerns;

use App\Support\Ukrainian;

/**
 * Кнопка «Транслітерувати» біля англійського поля власної назви:
 * переносить українське значення латиницею за правилами КМУ 2010 (Фелікс → Feliks).
 */
trait TransliteratesFields
{
    public function transliterate(string $field): void
    {
        abort_unless($this->canTransliterate($field), 403);

        data_set($this, $field.'_en', Ukrainian::transliterate(trim((string) data_get($this, $field))));
    }

    /** Поля-власні назви, для яких транслітерація має сенс (кличка, назва, адреса, автор цитати). */
    abstract protected function canTransliterate(string $field): bool;
}
