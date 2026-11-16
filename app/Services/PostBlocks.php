<?php

namespace App\Services;

use App\Models\Animal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Схема вмісту статті з блокового конструктора (FR-15).
 *
 * Стаття зберігається як масив блоків {type, data} у полі JSONB, а не як HTML.
 * Перед збереженням кожен блок перевіряється за схемою свого типу; під час показу
 * текст завжди екранується, тому HTML чи скрипт у тексті блоку не виконається (захист від XSS).
 */
class PostBlocks
{
    public const MAX_BLOCKS = 60;

    /** Типи блоків і їхні назви в конструкторі. */
    public const TYPES = [
        'heading' => 'Заголовок',
        'paragraph' => 'Текст',
        'image' => 'Зображення',
        'quote' => 'Цитата',
        'list' => 'Список',
        'animal' => 'Картка тварини',
    ];

    /** Порожній блок заданого типу – з нього конструктор починає редагування. */
    public static function blank(string $type): array
    {
        return [
            'type' => $type,
            'data' => match ($type) {
                'heading' => ['text' => '', 'level' => 2],
                'paragraph' => ['text' => ''],
                'image' => ['path' => '', 'alt' => '', 'caption' => ''],
                'quote' => ['text' => '', 'author' => ''],
                'list' => ['style' => 'bullet', 'items' => ['']],
                'animal' => ['animal_id' => null],
            },
        ];
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
            $type = $block['type'] ?? null;
            if (! is_string($type) || ! array_key_exists($type, self::TYPES)) {
                throw ValidationException::withMessages(["blocks.$i" => 'Невідомий тип блоку.']);
            }

            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $validator = Validator::make($data, $this->rules($type), $this->messages(), $this->attributes());

            if ($validator->fails()) {
                $messages = [];
                foreach ($validator->errors()->messages() as $field => $errors) {
                    $messages["blocks.$i.data.$field"] = $errors;
                }
                throw ValidationException::withMessages($messages);
            }

            $clean[] = ['type' => $type, 'data' => $this->normalize($type, $validator->validated())];
        }

        return $clean;
    }

    /** Готує блоки до показу: підставляє адреси зображень і моделі тварин. */
    public function forRender(array $blocks): array
    {
        $animalIds = collect($blocks)->where('type', 'animal')->pluck('data.animal_id')->filter()->all();
        $animals = Animal::query()->visible()->whereKey($animalIds)->with(['species', 'locality', 'coverPhoto'])->get()->keyBy('id');

        return collect($blocks)->map(function (array $block) use ($animals) {
            $data = $block['data'] ?? [];

            return match ($block['type'] ?? null) {
                'image' => ['type' => 'image', 'data' => $data + ['url' => Storage::disk('public')->url($data['path'] ?? '')]],
                'animal' => isset($animals[$data['animal_id'] ?? 0]) ? ['type' => 'animal', 'animal' => $animals[$data['animal_id']]] : null,
                default => $block,
            };
        })->filter()->values()->all();
    }

    private function rules(string $type): array
    {
        return match ($type) {
            'heading' => ['text' => ['required', 'string', 'max:160'], 'level' => ['required', 'integer', Rule::in([2, 3])]],
            'paragraph' => ['text' => ['required', 'string', 'max:5000']],
            'image' => [
                'path' => ['required', 'string', 'max:255', 'starts_with:posts/'],
                'alt' => ['required', 'string', 'max:160'],
                'caption' => ['nullable', 'string', 'max:200'],
            ],
            'quote' => ['text' => ['required', 'string', 'max:600'], 'author' => ['nullable', 'string', 'max:100']],
            'list' => [
                'style' => ['required', Rule::in(['bullet', 'ordered'])],
                'items' => ['required', 'array', 'min:1', 'max:30'],
                'items.*' => ['required', 'string', 'max:300'],
            ],
            'animal' => ['animal_id' => ['required', 'integer', Rule::exists('animals', 'id')]],
        };
    }

    private function normalize(string $type, array $data): array
    {
        $trim = fn ($v) => is_string($v) ? trim($v) : $v;

        return match ($type) {
            'heading' => ['text' => $trim($data['text']), 'level' => (int) $data['level']],
            'paragraph' => ['text' => $trim($data['text'])],
            'image' => ['path' => $data['path'], 'alt' => $trim($data['alt']), 'caption' => $trim($data['caption'] ?? '') ?: null],
            'quote' => ['text' => $trim($data['text']), 'author' => $trim($data['author'] ?? '') ?: null],
            'list' => ['style' => $data['style'], 'items' => array_values(array_map($trim, $data['items']))],
            'animal' => ['animal_id' => (int) $data['animal_id']],
        };
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
            'author' => 'автор', 'style' => 'вигляд списку', 'items' => 'пункти', 'items.*' => 'пункт', 'animal_id' => 'тварина'];
    }
}
