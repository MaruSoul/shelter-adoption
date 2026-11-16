<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Блог (рубрики, статті з блоковим вмістом) і налаштування притулку.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('slug', 80)->unique();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 190)->unique();
            $table->string('excerpt', 300)->nullable();
            $table->string('cover_path')->nullable();
            $table->jsonb('blocks');                  // структурований вміст конструктора
            $table->string('status', 20)->default('draft'); // App\Enums\PostStatus
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('shelter_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('tagline', 160)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('notification_email', 150)->nullable(); // куди надходять листи про анкети
            $table->string('instagram_url', 255)->nullable();
            $table->string('telegram_url', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelter_settings');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};
