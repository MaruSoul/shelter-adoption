<?php

use App\Livewire\AdoptionForm;
use App\Livewire\Cabinet\Profile;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\Region;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(fn () => RateLimiter::clear('adoption-form:127.0.0.1'));

it('користувач зберігає телефон і область у профілі', function () {
    $me = volunteer(['email_verified_at' => now()]);
    $region = Region::firstWhere('name', 'Одеська');

    Livewire::actingAs($me)->test(Profile::class)
        ->set('name', 'Андрій Коваль')
        ->set('phone', '067 123 45 67')
        ->set('region_id', (string) $region->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Профіль збережено');

    expect($me->fresh())
        ->name->toBe('Андрій Коваль')
        ->phone->toBe('+380671234567')
        ->region_id->toBe($region->id);
});

it('анкета підставляє імʼя, пошту, телефон і область з профілю', function () {
    $region = Region::firstWhere('name', 'Одеська');
    $me = volunteer(['name' => 'Андрій', 'phone' => '+380671234567', 'region_id' => $region->id, 'email_verified_at' => now()]);

    Livewire::actingAs($me)->test(AdoptionForm::class, ['animal' => Animal::factory()->create()])
        ->assertSet('name', 'Андрій')
        ->assertSet('email', $me->email)
        ->assertSet('phone', '+380671234567')
        ->assertSet('region_id', (string) $region->id)
        ->assertSet('rememberContacts', false);
});

it('за згодою запамʼятовує контакти з анкети в профілі, без згоди – ні', function () {
    Mail::fake();
    $region = Region::firstWhere('name', 'Одеська');
    $me = volunteer(['email_verified_at' => now()]);

    $form = fn () => Livewire::actingAs($me)->test(AdoptionForm::class, ['animal' => Animal::factory()->create()])
        ->set('phone', '+380671234567')
        ->set('region_id', (string) $region->id)
        ->set('contact_channels', ['phone'])
        ->set('consent', true);

    // Профіль порожній – галочка ввімкнена за замовчуванням
    $form()->assertSet('rememberContacts', true)->call('submit')->assertHasNoErrors();
    expect($me->fresh()->phone)->toBe('+380671234567');

    // Профіль уже заповнений – інший номер в анкеті не перезаписує його без галочки
    $form()->set('phone', '+380501112233')->assertSet('rememberContacts', false)->call('submit')->assertHasNoErrors();
    expect($me->fresh()->phone)->toBe('+380671234567')
        ->and(AdoptionApplication::latest('id')->first()->phone)->toBe('+380501112233');
});
