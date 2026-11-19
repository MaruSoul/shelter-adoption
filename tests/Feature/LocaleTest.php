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

it('перемикач запамʼятовує англійську й веде на англійську версію тієї самої сторінки', function () {
    $this->from(route('animals.index').'?sterilized=1')
        ->get(route('locale.switch', 'en'))
        ->assertRedirect('/en/tvaryny?sterilized=1')
        ->assertCookie(SetLocale::COOKIE, 'en', false);
});

it('мову публічної сторінки визначає адреса, а не cookie', function () {
    $animal = Animal::factory()->create();

    // Відкрита англійська сторінка запамʼятовує вибір для кабінету й входу
    $this->get('/en/tvaryny')->assertSee('lang="en"', false)->assertCookie(SetLocale::COOKIE, 'en', false);
    $this->withUnencryptedCookie(SetLocale::COOKIE, 'en')->get(url('/tvaryny/'.$animal->slug))
        ->assertSee('lang="uk"', false)->assertSee('Взяти додому');
});

it('англійська сторінка веде на англійські адреси й має теги hreflang', function () {
    $animal = Animal::factory()->create();

    $this->get(enRoute('animals.show', $animal))
        ->assertSee('href="'.url('/en/tvaryny/'.$animal->slug.'/vzyaty-dodomu').'"', false)
        ->assertSee('<link rel="alternate" hreflang="uk" href="'.url('/tvaryny/'.$animal->slug).'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/tvaryny/'.$animal->slug).'">', false);
    // Сторінки без мовних версій (вхід, кабінет) тегів hreflang не мають
    $this->get(route('login'))->assertDontSee('hreflang="en"', false);
});

it('карта сайту містить обидві мовні версії', function () {
    $animal = Animal::factory()->create();

    $this->get(route('sitemap'))->assertOk()
        ->assertSee('<loc>'.url('/en/tvaryny/'.$animal->slug).'</loc>', false)
        ->assertSee('hreflang="uk" href="'.url('/tvaryny/'.$animal->slug).'"', false);
});

it('не приймає непідтримувану мову', function () {
    $this->get('/mova/de')->assertNotFound();
});

it('показує інтерфейс англійською, а населені пункти – латиницею', function () {
    $animal = Animal::factory()->create();

    $this->get(enRoute('animals.show', $animal))
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
