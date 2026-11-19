<?php

namespace App\Services\Blocks;

use Illuminate\Support\Facades\Storage;

/** Зображення, завантажене через конструктор (лише з каталогу posts/), з описом для незрячих і підписом. */
class ImageBlock extends AbstractBlock
{
    protected const TEXT_FIELDS = ['alt', 'caption'];

    public function key(): string
    {
        return 'image';
    }

    public function label(): string
    {
        return 'Зображення';
    }

    public function blank(): array
    {
        return ['path' => '', 'alt' => '', 'alt_en' => '', 'caption' => '', 'caption_en' => ''];
    }

    public function rules(): array
    {
        return [
            'path' => ['required', 'string', 'max:255', 'starts_with:posts/'],
            'alt' => ['required', 'string', 'max:160'],
            'alt_en' => ['nullable', 'string', 'max:160'],
            'caption' => ['nullable', 'string', 'max:200'],
            'caption_en' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function normalize(array $data): array
    {
        return self::withoutEmptyTranslations([
            'path' => $data['path'],
            'alt' => self::trim($data['alt']),
            'alt_en' => self::optional($data['alt_en'] ?? null),
            'caption' => self::optional($data['caption'] ?? null),
            'caption_en' => self::optional($data['caption_en'] ?? null),
        ]);
    }

    public function isFilled(array $data): bool
    {
        return ! empty($data['path']);
    }

    public function forRender(array $block): ?array
    {
        $block['data']['url'] = Storage::disk('public')->url($block['data']['path'] ?? '');

        return $block;
    }
}
