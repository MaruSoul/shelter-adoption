<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Тварини та їхні фото.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->constrained()->restrictOnDelete();
            $table->foreignId('breed_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('locality_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name', 60);
            $table->string('slug', 90)->unique();
            $table->string('sex', 10);                 // App\Enums\Sex
            $table->date('birth_date');                // орієнтовна дата народження
            $table->boolean('is_sterilized')->default(false);
            $table->boolean('is_vaccinated')->default(false);

            $table->text('story')->nullable();         // «Коротка історія»
            $table->text('behavior')->nullable();      // «Особливості поведінки»
            $table->text('adoption_terms')->nullable(); // «Умови прилаштування»
            $table->string('current_location', 255)->nullable(); // де тварина зараз (для волонтерів)

            $table->string('source', 20)->default('shelter'); // App\Enums\AnimalSource
            $table->string('status', 20)->default('available'); // App\Enums\AnimalStatus
            $table->text('moderation_note')->nullable(); // причина відхилення
            $table->timestamp('published_at')->nullable();
            $table->timestamp('adopted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'species_id']);
            $table->index('birth_date');
        });

        Schema::create('animal_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('thumb_path')->nullable(); // мініатюра, генерується у черзі
            $table->string('alt', 160)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['animal_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_photos');
        Schema::dropIfExists('animals');
    }
};
