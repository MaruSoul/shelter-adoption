<?php

use App\Enums\AnimalStatus;
use App\Livewire\AnimalCatalog;
use App\Models\Animal;
use App\Models\Species;
use Livewire\Livewire;

it('показує в каталозі лише тварин, які шукають дім (FR-01)', function () {
    $visible = Animal::factory()->create(['name' => 'Видима']);
    Animal::factory()->adopted()->create(['name' => 'Прилаштована']);
    Animal::factory()->fromVolunteer(volunteer())->create(['name' => 'НаМодерації']);
    Animal::factory()->status(AnimalStatus::Archived)->create(['name' => 'Архівна']);

    Livewire::test(AnimalCatalog::class)
        ->assertSee('Видима')
        ->assertDontSee('Прилаштована')
        ->assertDontSee('НаМодерації')
        ->assertDontSee('Архівна');
});

it('фільтрує за видом і віковою групою', function () {
    $cat = Species::firstWhere('slug', 'cat');
    $dog = Species::firstWhere('slug', 'dog');
    Animal::factory()->create(['name' => 'Кошеня', 'species_id' => $cat->id, 'birth_date' => now()->subMonths(5)]);
    Animal::factory()->create(['name' => 'СтарийКіт', 'species_id' => $cat->id, 'birth_date' => now()->subYears(7)]);
    Animal::factory()->create(['name' => 'Цуценя', 'species_id' => $dog->id, 'birth_date' => now()->subMonths(4)]);

    Livewire::withQueryParams(['age' => ['up-to-1']])
        ->test(AnimalCatalog::class, ['speciesSlug' => 'cat'])
        ->assertSee('Кошеня')
        ->assertDontSee('СтарийКіт')
        ->assertDontSee('Цуценя');
});

it('кожен вид має окрему сторінку каталогу, а старі посилання з ?species= ведуть на неї', function () {
    Animal::factory()->create(['name' => 'Кошеня', 'species_id' => Species::firstWhere('slug', 'cat')->id]);

    $this->get('/tvaryny/cat')->assertOk()->assertSee('Котики шукають дім')->assertSee('Кошеня');
    $this->get('/en/tvaryny/cat')->assertOk()->assertSee('Cats looking for a home');
    $this->get('/tvaryny?species=cat&sterilized=1')->assertRedirect('/tvaryny/cat?sterilized=1')->assertStatus(301);
    $this->get('/tvaryny/kenguru')->assertNotFound();
});

it('показує зрозуміле повідомлення для порожньої вибірки', function () {
    Animal::factory()->create(['is_sterilized' => false]);

    Livewire::test(AnimalCatalog::class)
        ->set('sterilized', true)
        ->assertSee('Нікого не знайшли');
});

it('перемикач «знайшли дім» показує прилаштованих', function () {
    Animal::factory()->adopted()->create(['name' => 'ВжеВдома']);

    Livewire::test(AnimalCatalog::class)->set('adopted', true)->assertSee('ВжеВдома');
});

it('ділить каталог на сторінки по 12', function () {
    Animal::factory()->count(13)->create();

    Livewire::test(AnimalCatalog::class)->assertViewHas('activeCount', 0)
        ->tap(fn ($c) => expect($c->instance()->animals->count())->toBe(12)->and($c->instance()->animals->total())->toBe(13));
});
