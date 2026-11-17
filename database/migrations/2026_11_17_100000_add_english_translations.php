<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Англійська версія текстів, які вводить адміністратор.
 * Українська лишається основною мовою (обовʼязкові поля), англійська – необовʼязкова пара з суфіксом _en.
 * Текст блоків статті перекладається всередині JSON блоку (text_en, items_en тощо), структура статті одна.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->string('name_en', 60)->nullable()->after('name');
            $table->text('story_en')->nullable()->after('story');
            $table->text('behavior_en')->nullable()->after('behavior');
            $table->text('adoption_terms_en')->nullable()->after('adoption_terms');
            // Показується на сайті лише для карток волонтерів («зараз: перетримка у волонтера, Одеса»)
            $table->string('current_location_en', 255)->nullable()->after('current_location');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('title_en', 160)->nullable()->after('title');
            $table->string('excerpt_en', 300)->nullable()->after('excerpt');
        });

        Schema::table('shelter_settings', function (Blueprint $table) {
            $table->string('name_en', 100)->nullable()->after('name');
            $table->string('tagline_en', 160)->nullable()->after('tagline');
            $table->string('address_en', 255)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('animals', fn (Blueprint $t) => $t->dropColumn(['name_en', 'story_en', 'behavior_en', 'adoption_terms_en', 'current_location_en']));
        Schema::table('posts', fn (Blueprint $t) => $t->dropColumn(['title_en', 'excerpt_en']));
        Schema::table('shelter_settings', fn (Blueprint $t) => $t->dropColumn(['name_en', 'tagline_en', 'address_en']));
    }
};
