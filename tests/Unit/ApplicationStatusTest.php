<?php

use App\Enums\ApplicationStatus as S;

it('дозволяє лише переходи з матриці станів', function (S $from, S $to, bool $allowed) {
    expect($from->canTransitionTo($to))->toBe($allowed);
})->with(function () {
    $allowed = [
        'new' => ['in_progress', 'declined'],
        'in_progress' => ['meeting_scheduled', 'adopted', 'declined'],
        'meeting_scheduled' => ['in_progress', 'adopted', 'declined'],
        'adopted' => [], 'declined' => [], 'closed' => [],
    ];
    // Повний перебір 6 × 6 = 36 пар станів
    foreach (S::cases() as $from) {
        foreach (S::cases() as $to) {
            yield "{$from->value} → {$to->value}" => [$from, $to, in_array($to->value, $allowed[$from->value], true)];
        }
    }
});

it('вважає активними лише нову, в роботі та знайомство', function () {
    expect(array_map(fn ($s) => $s->value, S::active()))->toBe(['new', 'in_progress', 'meeting_scheduled'])
        ->and(S::Adopted->isFinal())->toBeTrue()
        ->and(S::New->isFinal())->toBeFalse();
});
