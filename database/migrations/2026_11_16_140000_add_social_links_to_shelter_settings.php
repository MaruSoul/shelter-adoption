<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Посилання на соцмережі для блоку «Слідкуй за нами» (Instagram і Telegram уже були). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shelter_settings', function (Blueprint $table) {
            $table->string('facebook_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('youtube_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shelter_settings', function (Blueprint $table) {
            $table->dropColumn(['facebook_url', 'tiktok_url', 'youtube_url']);
        });
    }
};
