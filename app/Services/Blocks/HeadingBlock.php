<?php

namespace App\Services\Blocks;

use Illuminate\Validation\Rule;

/** Заголовок розділу (h2) або підрозділу (h3). */
class HeadingBlock extends AbstractBlock
{
    protected const TEXT_FIELDS = ['text'];

    public function key(): string
    {
        return 'heading';
    }

    public function label(): string
    {
        return 'Заголовок';
    }

    public function blank(): array
    {
        return ['text' => '', 'text_en' => '', 'level' => 2];
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:160'],
            'text_en' => ['nullable', 'string', 'max:160'],
            'level' => ['required', 'integer', Rule::in([2, 3])],
        ];
    }

    public function normalize(array $data): array
    {
        return self::withoutEmptyTranslations([
            'text' => self::trim($data['text']),
            'text_en' => self::optional($data['text_en'] ?? null),
            'level' => (int) $data['level'],
        ]);
    }
}
