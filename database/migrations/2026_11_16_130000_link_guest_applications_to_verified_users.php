<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Разове виправлення даних: анкети, подані гостем з адресою вже підтвердженого акаунта,
 * раніше лишалися без власника. Привʼязуємо їх до відповідних акаунтів.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE adoption_applications AS a
            SET user_id = u.id
            FROM users AS u
            WHERE a.user_id IS NULL
              AND u.email_verified_at IS NOT NULL
              AND lower(u.email) = a.email
        SQL);
    }

    public function down(): void
    {
        // Привʼязку не скасовуємо: вона відповідає правилам FR-09
    }
};
