<?php

use App\Enums\AnimalStatus;
use App\Models\Animal;
use App\Services\Blocks\BlockType;
use App\Services\PostBlocks;
use Illuminate\Validation\ValidationException;

/**
 * Контракт типів блоків статті: кожен зареєстрований тип має все потрібне,
 * тож новий блок не можна додати наполовину (без шаблону чи валідації).
 */
it('кожен тип блоку має шаблони показу й редагування', function (string $key) {
    expect(view()->exists('blog.blocks.'.$key))->toBeTrue("немає шаблону показу для «{$key}»")
        ->and(view()->exists('livewire.admin.post-editor.blocks.'.$key))->toBeTrue("немає шаблону редагування для «{$key}»");
})->with(fn () => array_keys(PostBlocks::types()));

it('порожній блок кожного типу не вважається заповненим і не проходить валідацію', function (string $key) {
    $type = PostBlocks::type($key);

    expect($type)->toBeInstanceOf(BlockType::class)
        ->and($type->isFilled($type->blank()))->toBeFalse()
        ->and(fn () => app(PostBlocks::class)->validate([PostBlocks::blank($key)]))
        ->toThrow(ValidationException::class);
})->with(fn () => array_keys(PostBlocks::types()));

it('ключі типів унікальні й збігаються з назвами кнопок конструктора', function () {
    expect(array_keys(PostBlocks::labels()))->toBe(array_keys(PostBlocks::types()))
        ->and(PostBlocks::labels())->each->toBeString()->not->toBeEmpty();
});

it('невідомий тип блоку відхиляється', function () {
    expect(fn () => app(PostBlocks::class)->validate([['type' => 'iframe', 'data' => ['html' => '<script>']]]))
        ->toThrow(ValidationException::class);
});

/** Заповнений приклад кожного типу: українська, англійська й допоміжні поля. */
function sampleBlocks(): array
{
    $animal = Animal::factory()->create();

    return [
        'heading' => ['text' => ' Розділ ', 'text_en' => 'Section', 'level' => 3],
        'paragraph' => ['text' => 'Абзац', 'text_en' => 'Paragraph'],
        'image' => ['path' => 'posts/a.jpg', 'alt' => 'Кіт на сонці', 'alt_en' => 'Cat in the sun', 'caption' => 'Підпис', 'caption_en' => ''],
        'quote' => ['text' => 'Терпіння окупається', 'text_en' => 'Patience pays off', 'author' => 'Ветеринарка', 'author_en' => ''],
        'list' => ['style' => 'ordered', 'items' => ['Перший', 'Другий'], 'items_en' => ['First', '']],
        'animal' => ['animal_id' => $animal->id],
        'video' => ['url' => 'https://youtu.be/aaaaaaaaaaa', 'url_en' => 'https://youtu.be/bbbbbbbbbbb', 'caption' => 'Відео', 'caption_en' => 'Video'],
    ];
}

it('заповнений блок кожного типу проходить валідацію, перекладається й готується до показу', function () {
    $samples = sampleBlocks();
    expect(array_keys($samples))->toBe(array_keys(PostBlocks::types()), 'приклад потрібен для кожного зареєстрованого типу');

    $blocks = app(PostBlocks::class)->validate(
        collect($samples)->map(fn ($data, $type) => ['type' => $type, 'data' => $data])->values()->all()
    );

    // Нормалізація: пробіли обрізано, порожні переклади не зберігаються
    expect($blocks[0]['data'])->toBe(['text' => 'Розділ', 'text_en' => 'Section', 'level' => 3])
        ->and($blocks[2]['data'])->not->toHaveKey('caption_en')
        ->and($blocks[4]['data']['items_en'])->toBe(['First', '']);

    // Англійською: перекладене підставлено, неперекладене позначено lang=uk
    $en = collect(PostBlocks::localize($blocks, 'en'))->keyBy('type');
    expect($en['heading']['data']['text'])->toBe('Section')->and($en['heading'])->not->toHaveKey('lang')
        ->and($en['image']['data']['alt'])->toBe('Cat in the sun')->and($en['image']['lang'])->toBe('uk')
        ->and($en['quote']['lang'])->toBe('uk')
        ->and($en['list']['data']['items'])->toBe(['First', 'Другий'])->and($en['list']['lang'])->toBe('uk')
        ->and($en['video']['data']['url'])->toBe('https://youtu.be/bbbbbbbbbbb');

    // Покриття перекладом: заголовок 1/1, абзац 1/1, зображення 1/2, цитата 1/2, список 1/2, відео 1/1
    expect(PostBlocks::englishCoverage($blocks))->toBe([6, 9]);

    // Підготовка до показу: адреса зображення, модель тварини, вставка відео
    $render = collect(app(PostBlocks::class)->forRender($blocks))->keyBy('type');
    expect($render['image']['data']['url'])->toEndWith('posts/a.jpg')
        ->and($render['animal']['animal']->id)->toBe($samples['animal']['animal_id'])
        ->and($render['video']['data']['provider'])->toBe('youtube');

    // Конструктор отримує порожні англійські поля для статей, створених до англійської версії
    $legacy = PostBlocks::withEnglishKeys([['type' => 'list', 'data' => ['style' => 'bullet', 'items' => ['a', 'b']]]]);
    expect($legacy[0]['data']['items_en'])->toBe(['', '']);
});

it('тварина, прихована з каталогу, у статті не показується', function () {
    $hidden = Animal::factory()->create(['status' => AnimalStatus::Moderation]);

    expect(app(PostBlocks::class)->forRender([['type' => 'animal', 'data' => ['animal_id' => $hidden->id]]]))->toBe([]);
});
