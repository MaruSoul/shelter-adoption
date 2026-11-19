<?php

namespace App\Services\Blocks;

use Illuminate\Validation\Rule;

/** Маркований або нумерований список; англійський пункт стоїть під тим самим номером, що й український. */
class ListBlock extends AbstractBlock
{
    protected const LIST_FIELDS = ['items'];

    public function key(): string
    {
        return 'list';
    }

    public function label(): string
    {
        return 'Список';
    }

    public function blank(): array
    {
        return ['style' => 'bullet', 'items' => [''], 'items_en' => ['']];
    }

    public function rules(): array
    {
        return [
            'style' => ['required', Rule::in(['bullet', 'ordered'])],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*' => ['required', 'string', 'max:300'],
            'items_en' => ['nullable', 'array', 'max:30'],
            'items_en.*' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function normalize(array $data): array
    {
        $items = array_values(array_map(self::trim(...), $data['items']));
        $en = array_values($data['items_en'] ?? []);
        $itemsEn = array_map(fn ($i) => self::optional($en[$i] ?? null) ?? '', array_keys($items));

        return self::withoutEmptyTranslations([
            'style' => $data['style'],
            'items' => $items,
            'items_en' => array_filter($itemsEn) ? $itemsEn : null,
        ]);
    }

    public function isFilled(array $data): bool
    {
        return count(array_filter($data['items'] ?? [])) > 0;
    }
}
