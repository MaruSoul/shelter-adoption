<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Сортування назв за українським алфавітом (ICU-колація PostgreSQL),
 * інакше «Івано-Франківська» опиняється перед «Автономна Республіка Крим».
 */
trait SortsUkrainian
{
    public function scopeAlphabetical(Builder $query, string $column = 'name'): void
    {
        $query->orderByRaw($query->getGrammar()->wrap($column).' COLLATE "uk-UA-x-icu"');
    }
}
