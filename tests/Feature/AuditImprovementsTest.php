<?php

use App\Enums\ApplicationStatus;
use App\Enums\ContactChannel;
use App\Livewire\Admin\ApplicationIndex;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\Post;
use App\Models\PostCategory;
use Livewire\Livewire;

it('показує на головній готові добірки з кількістю тварин замість форми пошуку', function () {
    Animal::factory()->count(2)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Швидкий вибір')
        ->assertDontSee('Кого шукаєте')
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"AnimalShelter"', false);
});

it('рахує час читання статті щонайменше як одну хвилину', function () {
    $short = new Post(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Коротко']]]]);
    $long = new Post(['blocks' => [
        ['type' => 'paragraph', 'data' => ['text' => str_repeat('слово ', 540)]],
        ['type' => 'list', 'data' => ['style' => 'bullet', 'items' => [str_repeat('пункт ', 180)]]],
    ]]);

    expect($short->readingMinutes())->toBe(1)
        ->and($long->readingMinutes())->toBe(4);
});

it('показує «Читайте також» спершу з тієї ж рубрики й розмітку статті', function () {
    $post = makePost(['title' => 'Головна стаття']);
    makePost(['title' => 'Сусідня з рубрики']);
    $other = PostCategory::create(['slug' => 'inshe', 'name' => 'Інше']);
    makePost(['title' => 'З іншої рубрики', 'post_category_id' => $other->id]);

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('Читайте також')
        ->assertSeeInOrder(['Сусідня з рубрики', 'З іншої рубрики'])
        ->assertSee('"@type":"BlogPosting"', false)
        ->assertSee('хв читання');
});

it('фільтр «У роботі» з огляду показує всі незакриті етапи, крім нових', function () {
    $animal = Animal::factory()->create();
    $new = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::New)->create(['name' => 'Нова Анкета']);
    AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::InProgress)->create(['name' => 'Розгляд Анкета']);
    AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::MeetingScheduled)->create(['name' => 'Зустріч Анкета']);

    Livewire::actingAs(admin())->withQueryParams(['status' => 'working'])->test(ApplicationIndex::class)
        ->assertSee('Розгляд Анкета')
        ->assertSee('Зустріч Анкета')
        ->assertDontSee($new->name);
});

it('будує посилання для звʼязку через Viber і Telegram', function () {
    expect(ContactChannel::Viber->link('+380501234567'))->toBe('viber://chat?number=%2B380501234567')
        ->and(ContactChannel::Telegram->link('+380501234567'))->toBe('https://t.me/+380501234567')
        ->and(ContactChannel::Phone->link('+380501234567'))->toBe('tel:+380501234567');
});
