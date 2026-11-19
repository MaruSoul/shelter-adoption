<?php

namespace App\Services\Blocks;

/** Цитата з необовʼязковим автором. */
class QuoteBlock extends AbstractBlock
{
    protected const TEXT_FIELDS = ['text', 'author'];

    public function key(): string
    {
        return 'quote';
    }

    public function label(): string
    {
        return 'Цитата';
    }

    public function blank(): array
    {
        return ['text' => '', 'text_en' => '', 'author' => '', 'author_en' => ''];
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:600'],
            'text_en' => ['nullable', 'string', 'max:600'],
            'author' => ['nullable', 'string', 'max:100'],
            'author_en' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function normalize(array $data): array
    {
        return self::withoutEmptyTranslations([
            'text' => self::trim($data['text']),
            'text_en' => self::optional($data['text_en'] ?? null),
            'author' => self::optional($data['author'] ?? null),
            'author_en' => self::optional($data['author_en'] ?? null),
        ]);
    }
}
