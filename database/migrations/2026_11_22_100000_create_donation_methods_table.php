<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Способи фінансової допомоги притулку (банка Monobank, реквізити, PayPal, інше посилання)
 * і посилання на звіти. Набір полів кожного способу описує enum App\Enums\DonationType;
 * поля, що залежать від способу (IBAN, посилання тощо), зберігаються в `details`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('title', 80)->nullable();       // назва вкладки: «Monobank», «На корм на зиму»
            $table->string('title_en', 80)->nullable();
            $table->text('note')->nullable();               // короткий опис над кнопкою чи реквізитами
            $table->text('note_en')->nullable();
            $table->json('details');                        // поля способу: посилання, IBAN, отримувач…
            $table->timestamps();

            $table->index(['is_visible', 'position']);
        });

        Schema::table('shelter_settings', function (Blueprint $table) {
            $table->string('reports_url', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_methods');
        Schema::table('shelter_settings', fn (Blueprint $t) => $t->dropColumn('reports_url'));
    }
};
