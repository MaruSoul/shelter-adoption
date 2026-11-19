<?php

namespace App\Services\Blocks;

/** Абзац тексту; посилання й жирний розпізнає App\Support\SimpleMarkup під час показу. */
class ParagraphBlock extends AbstractBlock
{
    protected const TEXT_FIELDS = ['text'];

    public function key(): string
    {
        return 'paragraph';
    }

    public function label(): string
    {
        return 'Текст';
    }

    public function blank(): array
    {
        return ['text' => '', 'text_en' => ''];
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:5000'],
            'text_en' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function normalize(array $data): array
    {
        return self::withoutEmptyTranslations([
            'text' => self::trim($data['text']),
            'text_en' => self::optional($data['text_en'] ?? null),
        ]);
    }
}
