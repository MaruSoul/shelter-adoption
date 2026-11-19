<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO-налаштування сторінок, які редагує адміністратор:
 * - seo_pages: Title, Description, H1 і SEO-текст для головної, каталогу, розділів видів і блогу;
 * - posts: власні Title й Description статті для пошуку (порожні – беруться заголовок і анонс);
 * - shelter_settings: підключення Google Search Console й Google Analytics.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique(); // home, animals, species:cat, blog
            $table->string('title', 160)->nullable();
            $table->string('title_en', 160)->nullable();
            $table->string('description', 300)->nullable();
            $table->string('description_en', 300)->nullable();
            $table->string('h1', 160)->nullable();
            $table->string('h1_en', 160)->nullable();
            $table->text('text')->nullable();
            $table->text('text_en')->nullable();
            $table->timestamps();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('meta_title', 160)->nullable();
            $table->string('meta_title_en', 160)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->string('meta_description_en', 300)->nullable();
        });

        Schema::table('shelter_settings', function (Blueprint $table) {
            $table->string('google_site_verification', 120)->nullable();
            $table->string('google_analytics_id', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
        Schema::table('posts', fn (Blueprint $t) => $t->dropColumn(['meta_title', 'meta_title_en', 'meta_description', 'meta_description_en']));
        Schema::table('shelter_settings', fn (Blueprint $t) => $t->dropColumn(['google_site_verification', 'google_analytics_id']));
    }
};
