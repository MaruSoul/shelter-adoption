<?php

use App\Support\Ukrainian;
use Carbon\CarbonImmutable;

it('узгоджує іменник з числом', function (int $n, string $expected) {
    expect(Ukrainian::plural($n, ['рік', 'роки', 'років']))->toBe($expected);
})->with([
    [1, 'рік'], [2, 'роки'], [4, 'роки'], [5, 'років'], [11, 'років'],
    [12, 'років'], [14, 'років'], [21, 'рік'], [22, 'роки'], [111, 'років'],
]);

it('формує вік тварини', function (string $birth, string $expected) {
    $now = CarbonImmutable::parse('2026-11-16');
    expect(Ukrainian::age(CarbonImmutable::parse($birth), $now))->toBe($expected);
})->with([
    ['2026-11-01', 'менше місяця'],
    ['2026-04-10', '7 місяців'],
    ['2025-11-16', '1 рік'],
    ['2024-08-01', '2 роки 3 місяці'],
    ['2018-01-01', '8 років 10 місяців'],
]);
