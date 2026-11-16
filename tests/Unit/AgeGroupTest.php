<?php

use App\Enums\AgeGroup;
use Carbon\CarbonImmutable;

$today = CarbonImmutable::parse('2026-11-16');

it('перетворює вікову групу на діапазон дат народження', function (AgeGroup $group, ?string $from, string $to) use ($today) {
    [$earliest, $latest] = $group->birthDateRange($today);

    expect($earliest?->toDateString())->toBe($from)
        ->and($latest->toDateString())->toBe($to);
})->with([
    'до 1 року' => [AgeGroup::UpToOne, '2025-11-17', '2026-11-16'],
    '1–2 роки' => [AgeGroup::OneToTwo, '2024-11-17', '2025-11-16'],
    '2–5 років' => [AgeGroup::TwoToFive, '2021-11-17', '2024-11-16'],
    'понад 5 років' => [AgeGroup::OverFive, null, '2021-11-16'],
]);

it('межа: тварина, якій сьогодні рівно рік, належить до групи 1–2', function () use ($today) {
    $birth = $today->subYear();
    [$from, $to] = AgeGroup::OneToTwo->birthDateRange($today);
    [, $youngTo] = AgeGroup::UpToOne->birthDateRange($today);
    [$youngFrom] = AgeGroup::UpToOne->birthDateRange($today);

    expect($birth->betweenIncluded($from, $to))->toBeTrue()
        ->and($birth->betweenIncluded($youngFrom, $youngTo))->toBeFalse();
});
