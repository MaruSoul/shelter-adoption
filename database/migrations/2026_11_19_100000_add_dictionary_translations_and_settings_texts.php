<?php

use App\Support\DictionaryTranslations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Довідники редагуються в панелі адміністратора, тому англійські назви переходять із файлу
 * перекладів lang/en.json у базу (поле name_en). Наявні значення переносяться автоматично.
 *
 * Налаштування притулку отримують два двомовні тексти: «Як допомогти притулку» на головній
 * і типові умови прилаштування, які показуються в картках без власних умов.
 */
return new class extends Migration
{
    private const TABLES = [
        'species' => ['name' => 50, 'name_plural' => 50],
        'breeds' => ['name' => 80],
        'colors' => ['name' => 50],
        'post_categories' => ['name' => 60],
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $fields) {
            Schema::table($table, function (Blueprint $t) use ($fields) {
                foreach ($fields as $field => $length) {
                    $t->string($field.'_en', $length)->nullable()->after($field);
                }
            });
        }

        Schema::table('shelter_settings', function (Blueprint $t) {
            $t->text('help_text')->nullable();
            $t->text('help_text_en')->nullable();
            $t->text('default_adoption_terms')->nullable();
            $t->text('default_adoption_terms_en')->nullable();
        });

        DictionaryTranslations::backfill();
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $fields) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(array_map(fn ($f) => $f.'_en', array_keys($fields))));
        }
        Schema::table('shelter_settings', fn (Blueprint $t) => $t->dropColumn(['help_text', 'help_text_en', 'default_adoption_terms', 'default_adoption_terms_en']));
    }
};
