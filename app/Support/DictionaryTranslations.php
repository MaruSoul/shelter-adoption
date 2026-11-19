<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Заповнює англійські назви довідників (поля *_en) з файлу перекладів lang/en.json.
 * Порожні поля лише доповнюються: те, що адміністратор ввів у панелі, не перезаписується.
 */
class DictionaryTranslations
{
    public const FIELDS = [
        'species' => ['name', 'name_plural'],
        'breeds' => ['name'],
        'colors' => ['name'],
        'post_categories' => ['name'],
    ];

    public static function backfill(): void
    {
        $path = lang_path('en.json');
        $translations = is_file($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];

        foreach (self::FIELDS as $table => $fields) {
            foreach (DB::table($table)->get() as $row) {
                $update = [];
                foreach ($fields as $field) {
                    if (blank($row->{$field.'_en'}) && isset($translations[$row->{$field}])) {
                        $update[$field.'_en'] = $translations[$row->{$field}];
                    }
                }
                if ($update) {
                    DB::table($table)->where('id', $row->id)->update($update);
                }
            }
        }
    }
}
