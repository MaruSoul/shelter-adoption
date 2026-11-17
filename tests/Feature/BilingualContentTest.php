<?php

use App\Http\Middleware\SetLocale;
use App\Livewire\Admin\AnimalEditor;
use App\Livewire\Admin\AnimalIndex;
use App\Livewire\Admin\PostEditor;
use App\Livewire\Admin\Settings;
use App\Models\Animal;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\ShelterSetting;
use App\Services\PostBlocks;
use Livewire\Livewire;

/** Двомовний вміст, який вводить адміністратор: тварини, статті, налаштування. */
function inEnglish($test)
{
    return $test->withUnencryptedCookie(SetLocale::COOKIE, 'en');
}

it('показує англійські тексти тварини, а без перекладу – український з позначкою', function () {
    $translated = Animal::factory()->create([
        'name' => 'Фелікс', 'name_en' => 'Felix',
        'story' => 'Жив у волонтерки.', 'story_en' => 'Lived with a volunteer.',
        'behavior' => null, 'adoption_terms' => null,
    ]);
    $plain = Animal::factory()->create(['name' => 'Сніжка', 'story' => 'Шукає дім.', 'behavior' => null, 'adoption_terms' => null]);

    // Українська версія не змінилася (cookie мови в тесті зберігається між запитами, тож спершу – українська)
    $this->get(route('animals.show', $translated))->assertSee('Фелікс')->assertSee('Жив у волонтерки.')->assertDontSee('Felix');

    inEnglish($this)->get(route('animals.show', $translated))
        ->assertOk()->assertSee('Felix')->assertSee('Lived with a volunteer.')
        ->assertDontSee('Жив у волонтерки.')->assertDontSee('written by the shelter in Ukrainian');

    // Кличка без перекладу транслітерується, історія показується українською з позначкою мови
    inEnglish($this)->get(route('animals.show', $plain))
        ->assertOk()->assertSee('Snizhka')->assertSee('Шукає дім.')
        ->assertSee('written by the shelter in Ukrainian')->assertSee('lang="uk"', false);
});

it('адміністратор зберігає картку тварини двома мовами й транслітерує кличку', function () {
    $animal = Animal::factory()->create(['name' => 'Бурштин']);

    Livewire::actingAs(admin())->test(AnimalEditor::class, ['animal' => $animal])
        ->call('transliterate', 'form.name')
        ->assertSet('form.name_en', 'Burshtyn')
        ->set('form.name_en', 'Amber')
        ->set('form.story', 'Спокійний кіт.')
        ->set('form.story_en', 'A calm cat.')
        ->call('save')
        ->assertHasNoErrors();

    expect($animal->fresh())->name_en->toBe('Amber')->story_en->toBe('A calm cat.');
});

it('не дає транслітерувати довільну властивість компонента', function () {
    Livewire::actingAs(admin())->test(AnimalEditor::class)
        ->call('transliterate', 'status')
        ->assertForbidden();
});

it('фільтрує тварин без англійського перекладу', function () {
    $done = Animal::factory()->create(['story' => 'Текст', 'story_en' => 'Text', 'behavior' => null, 'adoption_terms' => null]);
    $todo = Animal::factory()->create(['story' => 'Текст', 'story_en' => null, 'behavior' => null, 'adoption_terms' => null]);

    expect(Animal::missingTranslation()->pluck('id')->all())->toBe([$todo->id]);

    Livewire::actingAs(admin())->test(AnimalIndex::class)
        ->set('translation', 'missing')
        ->assertSee($todo->name)
        ->assertDontSee($done->name);
});

it('конструктор статей зберігає переклад заголовка й блоків в одній структурі', function () {
    $category = PostCategory::create(['name' => 'Корисні поради', 'slug' => 'porady']);

    Livewire::actingAs(admin())->test(PostEditor::class)
        ->set('title', 'Перші дні вдома')
        ->set('title_en', 'The first days at home')
        ->set('post_category_id', (string) $category->id)
        ->set('blocks.0.data.text', 'Вступ')
        ->set('blocks.0.data.text_en', 'Introduction')
        ->call('addBlock', 'list')
        ->set('blocks.1.data.items.0', 'Лежанка')
        ->call('addListItem', 1)
        ->set('blocks.1.data.items.1', 'Миски')
        ->set('blocks.1.data.items_en.1', 'Bowls')
        ->call('save', 'publish')
        ->assertHasNoErrors();

    $post = Post::sole();
    expect($post->title_en)->toBe('The first days at home')
        ->and($post->blocks[0]['data'])->toBe(['text' => 'Вступ', 'text_en' => 'Introduction'])
        ->and($post->blocks[1]['data']['items_en'])->toBe(['', 'Bowls'])
        ->and($post->englishState())->toBe('partial');

    $this->get(route('blog.show', $post))->assertSee('Перші дні вдома')->assertSee('Миски')->assertDontSee('Bowls');

    inEnglish($this)->get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('The first days at home')->assertSee('Introduction')->assertSee('Bowls')
        ->assertSee('Лежанка') // неперекладений пункт лишається українським
        ->assertSee('Some parts of this article are available in Ukrainian only.');

});

it('стаття без англійської зберігається в тому ж форматі, що й раніше', function () {
    $clean = app(PostBlocks::class)->validate([
        ['type' => 'heading', 'data' => ['text' => 'Розділ', 'text_en' => '', 'level' => 2]],
        ['type' => 'list', 'data' => ['style' => 'bullet', 'items' => ['Пункт'], 'items_en' => ['']]],
    ]);

    expect($clean)->toBe([
        ['type' => 'heading', 'data' => ['text' => 'Розділ', 'level' => 2]],
        ['type' => 'list', 'data' => ['style' => 'bullet', 'items' => ['Пункт']]],
    ]);
});

it('назва, підзаголовок і адреса притулку редагуються двома мовами', function () {
    Livewire::actingAs(admin())->test(Settings::class)
        ->set('form.name', 'Домівка')
        ->set('form.notification_email', 'admin@example.com')
        ->call('transliterate', 'form.name')
        ->assertSet('form.name_en', 'Domivka')
        ->set('form.tagline', 'Притулок для котів')
        ->set('form.tagline_en', 'Cat shelter')
        ->call('save')
        ->assertHasNoErrors();

    expect(ShelterSetting::current())->name_en->toBe('Domivka')->tagline_en->toBe('Cat shelter');

    $this->get(route('home'))->assertSee('Притулок для котів')->assertDontSee('Cat shelter');
    inEnglish($this)->get(route('home'))->assertSee('Domivka')->assertSee('Cat shelter');
});
