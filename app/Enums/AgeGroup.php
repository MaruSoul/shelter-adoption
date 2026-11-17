<?php

namespace App\Enums;

use Carbon\CarbonImmutable;

/**
 * Вікові групи каталогу. Вік не зберігається в БД (він змінюється щодня),
 * тому група перетворюється на діапазон дат народження відносно поточної дати.
 */
enum AgeGroup: string
{
    case UpToOne = 'up-to-1';
    case OneToTwo = '1-2';
    case TwoToFive = '2-5';
    case OverFive = 'over-5';

    public function label(): string
    {
        return match ($this) {
            self::UpToOne => __('До 1 року'),
            self::OneToTwo => __('1–2 роки'),
            self::TwoToFive => __('2–5 років'),
            self::OverFive => __('Понад 5 років'),
        };
    }

    /**
     * Діапазон дат народження [від, до] (межі включно; null – без обмеження).
     * Вік у роках A ∈ [min, max) ⇔ дата народження ∈ (today − max, today − min].
     *
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    public function birthDateRange(?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();

        [$minYears, $maxYears] = match ($this) {
            self::UpToOne => [0, 1],
            self::OneToTwo => [1, 2],
            self::TwoToFive => [2, 5],
            self::OverFive => [5, null],
        };

        $latest = $today->subYears($minYears);                       // наймолодші в групі
        $earliest = $maxYears === null ? null : $today->subYears($maxYears)->addDay(); // найстарші

        return [$earliest, $latest];
    }
}
