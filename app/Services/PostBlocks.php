<?php

namespace App\Services;

use App\Services\Blocks\AnimalBlock;
use App\Services\Blocks\BlockType;
use App\Services\Blocks\HeadingBlock;
use App\Services\Blocks\ImageBlock;
use App\Services\Blocks\ListBlock;
use App\Services\Blocks\ParagraphBlock;
use App\Services\Blocks\PreparesBatch;
use App\Services\Blocks\QuoteBlock;
use App\Services\Blocks\VideoBlock;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Схема вмісту статті з блокового конструктора (FR-15).
 *
 * Стаття зберігається як масив блоків {type, data} у полі JSONB, а не як HTML.
 * Перед збереженням кожен блок перевіряється за схемою свого типу; під час показу
 * текст завжди екранується, тому HTML чи скрипт у тексті блоку не виконається (захист від XSS).
 *
 * Сам клас не знає подробиць жодного типу: усе, що стосується конкретного блоку, описано
 * в його класі (App\Services\Blocks\*), який реалізує контракт BlockType.
 * Щоб додати новий тип блоку: створити клас, додати його в BLOCKS і два шаблони (див. BlockType).
 */
class PostBlocks
{
    public const MAX_BLOCKS = 60;

    /** Зареєстровані типи блоків у порядку кнопок «Додати блок». */
    private const BLOCKS = [
        HeadingBlock::class,
        ParagraphBlock::class,
        ImageBlock::class,
        QuoteBlock::class,
        ListBlock::class,
        AnimalBlock::class,
        VideoBlock::class,
    ];

    /** @var array<string, BlockType>|null */
    private static ?array $types = null;

    /** @return array<string, BlockType> тип блоку за ключем */
    public static function types(): array
    {
        return self::$types ??= collect(self::BLOCKS)
            ->map(fn (string $class) => app($class))
            ->keyBy(fn (BlockType $type) => $type->key())
            ->all();
    }

    public static function type(string $key): ?BlockType
    {
        return self::types()[$key] ?? null;
    }

    /** @return array<string, string> ключ типу → назва в конструкторі */
    public static function labels(): array
    {
        return array_map(fn (BlockType $type) => $type->label(), self::types());
    }

    /** Порожній блок заданого типу – з нього конструктор починає редагування. */
    public static function blank(string $key): array
    {
        return ['type' => $key, 'data' => self::type($key)->blank()];
    }

    /** Чи заповнено блок настільки, щоб показати його в попередньому перегляді. */
    public static function isFilled(array $block): bool
    {
        return (bool) self::type($block['type'] ?? '')?->isFilled($block['data'] ?? []);
    }

    /**
     * Перевіряє й нормалізує блоки. Повертає чистий масив, придатний для збереження.
     *
     * @throws ValidationException
     */
    public function validate(array $blocks): array
    {
        if (count($blocks) > self::MAX_BLOCKS) {
            throw ValidationException::withMessages(['blocks' => 'Стаття може містити не більше '.self::MAX_BLOCKS.' блоків.']);
        }

        $clean = [];
        foreach (array_values($blocks) as $i => $block) {
            $type = is_string($block['type'] ?? null) ? self::type($block['type']) : null;
            if (! $type) {
                throw ValidationException::withMessages(["blocks.$i" => 'Невідомий тип блоку.']);
            }

            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $validator = Validator::make($data, $type->rules(), $this->messages(), $this->attributes());

            if ($validator->fails()) {
                $messages = [];
                foreach ($validator->errors()->messages() as $field => $errors) {
                    $messages["blocks.$i.data.$field"] = $errors;
                }
                throw ValidationException::withMessages($messages);
            }

            $clean[] = ['type' => $type->key(), 'data' => $type->normalize($validator->validated())];
        }

        return $clean;
    }

    /**
     * Доповнює збережені блоки порожніми англійськими полями, щоб конструктор
     * міг одразу показати пару «UA | EN» (статті, створені до появи англійської версії).
     */
    public static function withEnglishKeys(array $blocks): array
    {
        return array_map(function (array $block) {
            if ($type = self::type($block['type'] ?? '')) {
                $block['data'] = $type->withEnglishKeys($block['data'] ?? []);
            }

            return $block;
        }, $blocks);
    }

    /**
     * Підставляє в блоки текст потрібною мовою. Якщо перекладу немає, лишається український текст,
     * а блок отримує позначку lang=uk (для екранних читачів і підказки відвідувачу).
     */
    public static function localize(array $blocks, string $locale): array
    {
        if ($locale !== 'en') {
            return $blocks;
        }

        return array_map(function (array $block) {
            $type = self::type($block['type'] ?? '');
            if (! $type) {
                return $block;
            }
            [$block['data'], $untranslated] = $type->localize($block['data'] ?? []);
            if ($untranslated) {
                $block['lang'] = 'uk';
            }

            return $block;
        }, $blocks);
    }

    /**
     * Скільки текстових фрагментів блоків має український текст і скільки з них перекладено.
     *
     * @return array{0: int, 1: int} [перекладено, усього]
     */
    public static function englishCoverage(array $blocks): array
    {
        $done = $total = 0;
        foreach ($blocks as $block) {
            [$d, $t] = self::type($block['type'] ?? '')?->englishCoverage($block['data'] ?? []) ?? [0, 0];
            $done += $d;
            $total += $t;
        }

        return [$done, $total];
    }

    /** Готує блоки до показу: кожен тип додає потрібне (адресу зображення, модель тварини, вставку відео). */
    public function forRender(array $blocks): array
    {
        foreach (self::types() as $key => $type) {
            if ($type instanceof PreparesBatch) {
                $type->prepare(array_values(array_filter($blocks, fn ($b) => ($b['type'] ?? null) === $key)));
            }
        }

        return collect($blocks)
            ->map(fn (array $block) => self::type($block['type'] ?? '')?->forRender($block))
            ->filter()->values()->all();
    }

    private function messages(): array
    {
        return [
            'required' => 'Заповніть поле «:attribute».',
            'max' => 'Поле «:attribute» задовге (максимум :max).',
            'items.min' => 'Додайте хоча б один пункт списку.',
            'path.starts_with' => 'Зображення має бути завантажене через конструктор.',
        ];
    }

    private function attributes(): array
    {
        return ['text' => 'текст', 'level' => 'рівень', 'path' => 'зображення', 'alt' => 'опис зображення', 'caption' => 'підпис',
            'author' => 'автор', 'style' => 'вигляд списку', 'items' => 'пункти', 'items.*' => 'пункт', 'animal_id' => 'тварина',
            'url' => 'посилання на відео', 'url_en' => 'посилання на відео (EN)', 'text_en' => 'текст (EN)', 'alt_en' => 'опис зображення (EN)',
            'caption_en' => 'підпис (EN)', 'author_en' => 'автор (EN)', 'items_en.*' => 'пункт (EN)'];
    }
}
