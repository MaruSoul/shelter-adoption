<?php

use App\Livewire\Admin\Settings;
use App\Models\Animal;
use App\Models\ShelterSetting;
use Livewire\Livewire;

it('адміністратор змінює назву й контакти притулку без зміни коду (FR-16)', function () {
    Livewire::actingAs(admin())->test(Settings::class)
        ->set('form.name', 'Теплий дім')
        ->set('form.notification_email', 'ankety@example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect(ShelterSetting::current()->name)->toBe('Теплий дім');
    $this->get(route('home'))->assertSee('Теплий дім');
});

it('віддає sitemap лише з публічними сторінками (NFR-08)', function () {
    $public = Animal::factory()->create();
    $hidden = Animal::factory()->fromVolunteer(volunteer())->create();

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('animals.show', $public), false)
        ->assertDontSee(route('animals.show', $hidden), false);
});

it('має унікальні title та Open Graph для сторінки тварини', function () {
    $animal = Animal::factory()->create(['name' => 'Шерлок']);

    $this->get(route('animals.show', $animal))
        ->assertSee('<title>Шерлок – ', false)
        ->assertSee('property="og:title"', false);
});
