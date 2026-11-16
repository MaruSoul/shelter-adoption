<?php

use App\Enums\ApplicationStatus;
use App\Livewire\AdoptionForm;
use App\Mail\ApplicationReceivedMail;
use App\Mail\NewApplicationMail;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\Region;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

function fillForm($component, array $overrides = [])
{
    $data = array_merge([
        'name' => 'Олена Петренко',
        'email' => 'Olena@Example.com',
        'phone' => '+380671234567',
        'region_id' => (string) Region::firstWhere('name', 'Одеська')->id,
        'contact_channels' => ['phone', 'telegram'],
        'comment' => 'Живу в квартирі, є досвід.',
        'consent' => true,
    ], $overrides);

    foreach ($data as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

beforeEach(fn () => RateLimiter::clear('adoption-form:127.0.0.1'));

it('гість подає анкету без реєстрації, адміністратор отримує лист (FR-03, FR-05)', function () {
    Mail::fake();
    $animal = Animal::factory()->create();

    fillForm(Livewire::test(AdoptionForm::class, ['animal' => $animal]))
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true)
        ->assertSee('Анкету отримано');

    $application = AdoptionApplication::sole();
    expect($application->status)->toBe(ApplicationStatus::New)
        ->and($application->email)->toBe('olena@example.com')
        ->and($application->user_id)->toBeNull()
        ->and($application->contact_channels->map->value->all())->toBe(['phone', 'telegram'])
        ->and($application->statusChanges)->toHaveCount(1);

    Mail::assertQueued(NewApplicationMail::class);
    Mail::assertQueued(ApplicationReceivedMail::class, fn ($m) => $m->hasTo('olena@example.com'));
});

it('перевіряє обовʼязкові поля, формат телефону і згоду', function () {
    $animal = Animal::factory()->create();

    fillForm(Livewire::test(AdoptionForm::class, ['animal' => $animal]), [
        'name' => '', 'phone' => '0671234', 'contact_channels' => [], 'consent' => false, 'email' => 'не-пошта',
    ])
        ->call('submit')
        ->assertHasErrors(['name' => 'required', 'phone' => 'regex', 'contact_channels' => 'required', 'consent' => 'accepted', 'email']);

    expect(AdoptionApplication::count())->toBe(0);
});

it('нормалізує номер, введений з пробілами та без +38', function () {
    $animal = Animal::factory()->create();

    Livewire::test(AdoptionForm::class, ['animal' => $animal])
        ->set('phone', '067 123-45-67')
        ->assertSet('phone', '+380671234567');
});

it('не створює дублікат анкети з того ж e-mail на ту саму тварину (FR-04)', function () {
    $animal = Animal::factory()->create();
    AdoptionApplication::factory()->for($animal)->create(['email' => 'olena@example.com']);

    fillForm(Livewire::test(AdoptionForm::class, ['animal' => $animal]))
        ->call('submit')
        ->assertHasErrors('form')
        ->assertSee('вже надіслали анкету');

    expect(AdoptionApplication::count())->toBe(1);
});

it('дозволяє нову анкету, якщо попередню вже закрито', function () {
    $animal = Animal::factory()->create();
    AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::Declined)->create(['email' => 'olena@example.com']);

    fillForm(Livewire::test(AdoptionForm::class, ['animal' => $animal]))->call('submit')->assertHasNoErrors();

    expect(AdoptionApplication::count())->toBe(2);
});

it('перенаправляє з анкети на сторінку, якщо тварина вже знайшла дім', function () {
    $animal = Animal::factory()->adopted()->create();

    Livewire::test(AdoptionForm::class, ['animal' => $animal])->assertRedirect(route('animals.show', $animal));
});

it('мовчки відкидає анкету від бота, який заповнив поле-пастку', function () {
    $animal = Animal::factory()->create();

    fillForm(Livewire::test(AdoptionForm::class, ['animal' => $animal]), ['website' => 'http://spam.example'])
        ->call('submit')
        ->assertSet('submitted', true);

    expect(AdoptionApplication::count())->toBe(0);
});

it('обмежує кількість анкет з однієї IP-адреси', function () {
    Mail::fake();
    $animals = Animal::factory()->count(AdoptionForm::MAX_PER_HOUR + 1)->create();

    foreach ($animals->take(AdoptionForm::MAX_PER_HOUR) as $i => $animal) {
        fillForm(Livewire::test(AdoptionForm::class, ['animal' => $animal]), ['email' => "p{$i}@example.com"])->call('submit')->assertHasNoErrors();
    }

    fillForm(Livewire::test(AdoptionForm::class, ['animal' => $animals->last()]), ['email' => 'last@example.com'])
        ->call('submit')
        ->assertHasErrors('form')
        ->assertSee('Забагато анкет');
});

it('не дає подати анкету на тварину на модерації', function () {
    $animal = Animal::factory()->fromVolunteer(volunteer())->create();

    $this->get(route('animals.adopt', $animal))->assertNotFound();
});
