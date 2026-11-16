<?php

use App\Models\Animal;

it('відкриває сторінку тварини з характеристиками (FR-02)', function () {
    $animal = Animal::factory()->create(['name' => 'Ліра', 'story' => 'Знайшли біля ринку']);

    $this->get(route('animals.show', $animal))
        ->assertOk()
        ->assertSee('Ліра')
        ->assertSee('Знайшли біля ринку')
        ->assertSee('Взяти додому');
});

it('приховує картку на модерації від гостя та інших користувачів', function () {
    $author = volunteer();
    $animal = Animal::factory()->fromVolunteer($author)->create();

    $this->get(route('animals.show', $animal))->assertNotFound();
    $this->actingAs(volunteer())->get(route('animals.show', $animal))->assertNotFound();
    $this->actingAs($author)->get(route('animals.show', $animal))->assertOk()->assertSee('на модерації');
    $this->actingAs(admin())->get(route('animals.show', $animal))->assertOk();
});

it('не показує кнопку анкети для прилаштованої тварини', function () {
    $animal = Animal::factory()->adopted()->create();

    $this->get(route('animals.show', $animal))->assertOk()->assertDontSee('Взяти додому');
});
