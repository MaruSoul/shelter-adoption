<?php

use App\Http\Middleware\SetLocale;
use App\Livewire\AdoptionForm;
use App\Mail\ApplicationReceivedMail;
use App\Models\Animal;
use App\Models\Region;
use App\Support\Ukrainian;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('типово показує сайт українською', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('lang="uk"', false)
        ->assertSee('Знайти друга');
});

it('перемикач запамʼятовує англійську й повертає на ту саму сторінку', function () {
    $this->from(route('animals.index'))
        ->get(route('locale.switch', 'en'))
        ->assertRedirect(route('animals.index'))
        ->assertCookie(SetLocale::COOKIE, 'en', false);
});

it('не приймає непідтримувану мову', function () {
    $this->get('/mova/de')->assertNotFound();
});

it('показує інтерфейс англійською, а населені пункти – латиницею', function () {
    $animal = Animal::factory()->create();

    $this->withUnencryptedCookie(SetLocale::COOKIE, 'en')
        ->get(route('animals.show', $animal))
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('Take home')
        ->assertSee(Ukrainian::transliterate($animal->locality->name))
        ->assertDontSee('Взяти додому');
});

it('панель адміністратора лишається українською навіть з англійською cookie', function () {
    $this->actingAs(admin())
        ->withUnencryptedCookie(SetLocale::COOKIE, 'en')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('lang="uk"', false);
});

it('транслітерує назви за офіційними правилами', function () {
    expect(Ukrainian::transliterate('Одеса'))->toBe('Odesa')
        ->and(Ukrainian::transliterate('Знам’янка'))->toBe('Znamianka')
        ->and(Ukrainian::transliterate('Згурівка'))->toBe('Zghurivka')
        ->and(Ukrainian::transliterate('Миколаїв'))->toBe('Mykolaiv')
        ->and(Ukrainian::transliterate('Южне'))->toBe('Yuzhne');
});

it('надсилає заявнику лист мовою, якою подано анкету', function () {
    Mail::fake();
    app()->setLocale('en');
    $animal = Animal::factory()->create();

    Livewire::test(AdoptionForm::class, ['animal' => $animal])
        ->set('name', 'Test Person')
        ->set('email', 'person@example.com')
        ->set('phone', '050 123 45 67')
        ->set('region_id', Region::first()->id)
        ->set('contact_channels', ['telegram'])
        ->set('consent', true)
        ->call('submit')
        ->assertHasNoErrors();

    Mail::assertQueued(ApplicationReceivedMail::class, fn ($mail) => $mail->locale === 'en');
});
