<?php

use App\Models\User;
use Database\Seeders\DictionarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed(DictionarySeeder::class))
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

function admin(): User
{
    return User::factory()->admin()->create();
}

function volunteer(array $attributes = []): User
{
    return User::factory()->create($attributes);
}

/** Адреса англійської версії публічної сторінки: enRoute('animals.show', $animal) → /en/tvaryny/... */
function enRoute(string $name, mixed $parameters = []): string
{
    return app('url')->routeIn('en', $name, $parameters);
}
