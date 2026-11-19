<?php

use App\Livewire\Admin\Seo;
use App\Models\SeoPage;
use App\Models\ShelterSetting;
use Livewire\Livewire;

it('SEO сторінок доступне лише адміністратору', function () {
    $this->actingAs(volunteer())->get(route('admin.seo'))->assertForbidden();
    $this->actingAs(admin())->get(route('admin.seo'))->assertOk()->assertSee('Підключення Google');
});

it('Title, Description, H1 і SEO-текст із адмінки зʼявляються на сторінці', function () {
    Livewire::actingAs(admin())->test(Seo::class)
        ->set('page', 'species:cat')
        ->set('form.title', 'Взяти кота з притулку в Одесі безкоштовно')
        ->set('form.description', 'Котики шукають дім.')
        ->set('form.h1', 'Котики, які чекають на вас')
        ->set('form.text', "## Як узяти кота\n\nДивіться також [песиків](/tvaryny/dog).")
        ->call('save')->assertHasNoErrors();

    $this->get('/tvaryny/cat')->assertOk()
        ->assertSee('<title>Взяти кота з притулку в Одесі безкоштовно</title>', false)
        ->assertSee('<meta name="description" content="Котики шукають дім.">', false)
        ->assertSee('Котики, які чекають на вас')
        ->assertSee('<h2>Як узяти кота</h2>', false)
        ->assertSee('<a href="/tvaryny/dog">песиків</a>', false);

    // Англійська версія без англійських SEO-текстів лишається з типовими заголовками, без українського тексту
    $this->get('/en/tvaryny/cat')->assertOk()
        ->assertSee('Cats looking for a home')
        ->assertDontSee('Взяти кота з притулку')->assertDontSee('Як узяти кота');
    // На вибірці з фільтрами SEO-текст не повторюється
    $this->get('/tvaryny/cat?sterilized=1')->assertDontSee('Як узяти кота');
});

it('порожні SEO-поля не змінюють типового заголовка', function () {
    SeoPage::create(['key' => 'home']);

    $this->get(route('home'))->assertSee('Знайдіть друга, який чекає саме на вас')->assertSee('<title>Клуб хвостики', false);
});

it('підключає Google Search Console і показує банер згоди лише з ідентифікатором Analytics', function () {
    $this->get(route('home'))->assertDontSee('google-site-verification')->assertDontSee('googletagmanager');

    Livewire::actingAs(admin())->test(Seo::class)
        ->set('google.google_site_verification', 'AbC_123-xyz')
        ->set('google.google_analytics_id', 'G-ABC123XYZ')
        ->call('saveGoogle')->assertHasNoErrors();

    expect(ShelterSetting::current()->google_analytics_id)->toBe('G-ABC123XYZ');
    $this->get(route('home'))
        ->assertSee('<meta name="google-site-verification" content="AbC_123-xyz">', false)
        ->assertSee('analytics-consent')
        ->assertSee('Без вашої згоди нічого не збирається');
});

it('не приймає замість коду цілий тег чи неправильний ідентифікатор', function () {
    Livewire::actingAs(admin())->test(Seo::class)
        ->set('google.google_site_verification', '<meta name="google-site-verification" content="x">')
        ->set('google.google_analytics_id', 'UA-12345-1')
        ->call('saveGoogle')
        ->assertHasErrors(['google.google_site_verification', 'google.google_analytics_id']);
});

it('віддає llms.txt зі списком розділів і статей', function () {
    $this->get('/llms.txt')->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('## Каталог')
        ->assertSee(url('/tvaryny/cat'));
});
