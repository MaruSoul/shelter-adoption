<?php

use App\Enums\PostStatus;
use App\Livewire\Admin\PostEditor;
use App\Models\Animal;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PostBlocks;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function makePost(array $attributes = []): Post
{
    $category = PostCategory::firstOrCreate(['slug' => 'porady'], ['name' => 'Корисні поради']);
    $status = $attributes['status'] ?? PostStatus::Published;
    unset($attributes['status']);
    $post = new Post(array_merge([
        'post_category_id' => $category->id, 'title' => 'Стаття', 'slug' => 'stattia-'.uniqid(),
        'blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Текст статті']]],
    ], $attributes));
    $post->status = $status;
    $post->published_at = $post->status === PostStatus::Published ? now()->subDay() : null;
    $post->save();

    return $post;
}

it('показує опубліковану статтю і ховає чернетку від гостя (FR-14)', function () {
    $published = makePost(['title' => 'Опублікована']);
    $draft = makePost(['title' => 'Чернетка', 'status' => PostStatus::Draft]);

    $this->get(route('blog.index'))->assertOk()->assertSee('Опублікована')->assertDontSee('Чернетка');
    $this->get(route('blog.show', $draft))->assertNotFound();
    $this->actingAs(admin())->get(route('blog.show', $draft))->assertOk();
    $this->get(route('blog.show', $published))->assertOk()->assertSee('Текст статті');
});

it('екранує HTML і скрипти в тексті блоків (XSS)', function () {
    $post = makePost(['blocks' => [['type' => 'paragraph', 'data' => ['text' => '<script>alert(1)</script><b>жирний</b>']]]]);

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

it('перевіряє структуру блоків за схемою (FR-15)', function (array $blocks) {
    expect(fn () => app(PostBlocks::class)->validate($blocks))->toThrow(ValidationException::class);
})->with([
    'невідомий тип' => [[['type' => 'iframe', 'data' => ['src' => 'https://evil']]]],
    'порожній текст' => [[['type' => 'paragraph', 'data' => ['text' => '']]]],
    'неправильний рівень' => [[['type' => 'heading', 'data' => ['text' => 'А', 'level' => 1]]]],
    'зображення не з конструктора' => [[['type' => 'image', 'data' => ['path' => '../../.env', 'alt' => 'x']]]],
    'неіснуюча тварина' => [[['type' => 'animal', 'data' => ['animal_id' => 999999]]]],
    'список без пунктів' => [[['type' => 'list', 'data' => ['style' => 'bullet', 'items' => []]]]],
]);

it('нормалізує правильні блоки', function () {
    $animal = Animal::factory()->create();
    $clean = app(PostBlocks::class)->validate([
        ['type' => 'heading', 'data' => ['text' => '  Заголовок ', 'level' => '3', 'зайве' => 'x']],
        ['type' => 'animal', 'data' => ['animal_id' => (string) $animal->id]],
    ]);

    expect($clean)->toBe([
        ['type' => 'heading', 'data' => ['text' => 'Заголовок', 'level' => 3]],
        ['type' => 'animal', 'data' => ['animal_id' => $animal->id]],
    ]);
});

it('конструктор додає, переставляє блоки й публікує статтю', function () {
    $category = PostCategory::create(['name' => 'Новини', 'slug' => 'novyny']);

    Livewire::actingAs(admin())->test(PostEditor::class)
        ->set('title', 'Як підготуватися до кошеняти')
        ->assertSet('slug', 'iak-pidgotuvatisia-do-koseniati')
        ->set('post_category_id', (string) $category->id)
        ->set('blocks.0.data.text', 'Вступ')
        ->call('addBlock', 'heading')
        ->set('blocks.1.data.text', 'Що купити')
        ->call('moveBlock', 1, -1)
        ->call('save', 'publish')
        ->assertHasNoErrors();

    $post = Post::sole();
    expect($post->isPublished())->toBeTrue()
        ->and(array_column($post->blocks, 'type'))->toBe(['heading', 'paragraph']);
});

it('не публікує статтю з помилкою в блоці', function () {
    $category = PostCategory::create(['name' => 'Новини', 'slug' => 'novyny']);

    Livewire::actingAs(admin())->test(PostEditor::class)
        ->set('title', 'Стаття з помилкою')
        ->set('post_category_id', (string) $category->id)
        ->call('addBlock', 'quote')
        ->set('blocks.0.data.text', 'Нормальний текст')
        ->call('save', 'publish')
        ->assertHasErrors('blocks.1.data.text');

    expect(Post::count())->toBe(0);
});
