<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Волонтер може видалити власну картку. Якщо на неї вже подавали анкети,
 * картка лише ховається (deleted_at), щоб історія анкет не загубилась.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
