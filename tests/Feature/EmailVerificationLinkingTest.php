<?php

use App\Models\AdoptionApplication;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

it('привʼязує ранні анкети до акаунта лише після підтвердження пошти (FR-09)', function () {
    $application = AdoptionApplication::factory()->create(['email' => 'olena@example.com']);

    $user = User::factory()->unverified()->create(['email' => 'olena@example.com']);
    expect($application->fresh()->user_id)->toBeNull();

    $user->markEmailAsVerified();
    event(new Verified($user));

    expect($application->fresh()->user_id)->toBe($user->id);
});

it('не привʼязує анкети з іншою адресою', function () {
    $application = AdoptionApplication::factory()->create(['email' => 'someone@example.com']);
    $user = User::factory()->create(['email' => 'olena@example.com']);
    event(new Verified($user));

    expect($application->fresh()->user_id)->toBeNull();
});
