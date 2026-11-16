<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Анкети на прилаштування та історія зміни їхніх статусів.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adoption_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();

            $table->string('name', 100);
            $table->string('email', 150);
            $table->string('phone', 20);
            $table->string('social_url', 255)->nullable();
            $table->json('contact_channels');          // ["phone","telegram","viber"]
            $table->text('comment')->nullable();
            $table->timestamp('consent_at');           // момент згоди на обробку даних

            $table->string('status', 30)->default('new'); // App\Enums\ApplicationStatus
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });

        // Бізнес-правило на рівні СУБД: тварину може бути прилаштовано лише за однією анкетою,
        // навіть якщо два адміністратори натиснуть «Прилаштовано» одночасно.
        DB::statement(
            "CREATE UNIQUE INDEX adoption_applications_one_adopted_per_animal
             ON adoption_applications (animal_id) WHERE status = 'adopted'"
        );

        // Одна активна анкета від однієї адреси e-mail на одну тварину.
        DB::statement(
            "CREATE UNIQUE INDEX adoption_applications_one_active_per_email
             ON adoption_applications (animal_id, lower(email))
             WHERE status IN ('new', 'in_progress', 'meeting_scheduled')"
        );

        Schema::create('application_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adoption_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_changes');
        Schema::dropIfExists('adoption_applications');
    }
};
