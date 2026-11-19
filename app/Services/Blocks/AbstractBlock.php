<?php

namespace App\Services\Blocks;

/**
 * Спільна поведінка блоків із двомовним текстом.
 *
 * Підклас оголошує, які поля мають англійську пару (поле `x` → `x_en`):
 *   TEXT_FIELDS – рядки; LIST_FIELDS – масиви рядків, де англійський пункт стоїть під тим самим номером.
 * Звідси автоматично працюють доповнення англійськими ключами, підстановка перекладу й підрахунок покриття.
 */
abstract class AbstractBlock implements BlockType
{
    /** @var list<string> */
    protected const TEXT_FIELDS = [];

    /** @var list<string> */
    protected const LIST_FIELDS = [];

    public function isFilled(array $data): bool
    {
        return trim((string) ($data['text'] ?? '')) !== '';
    }

    public function withEnglishKeys(array $data): array
    {
        foreach (static::TEXT_FIELDS as $field) {
            $data[$field] = (string) ($data[$field] ?? '');
            $data[$field.'_en'] = (string) ($data[$field.'_en'] ?? '');
        }
        foreach (static::LIST_FIELDS as $field) {
            $en = array_values($data[$field.'_en'] ?? []);
            $data[$field.'_en'] = array_map(fn ($i) => (string) ($en[$i] ?? ''), array_keys($data[$field] ?? []));
        }

        return $data;
    }

    public function localize(array $data): array
    {
        $untranslated = false;
        foreach (static::TEXT_FIELDS as $field) {
            [$data[$field], $missing] = self::pick($data[$field] ?? null, $data[$field.'_en'] ?? null);
            $untranslated = $untranslated || $missing;
        }
        foreach (static::LIST_FIELDS as $field) {
            $en = $data[$field.'_en'] ?? [];
            foreach ($data[$field] ?? [] as $i => $item) {
                [$data[$field][$i], $missing] = self::pick($item, $en[$i] ?? null);
                $untranslated = $untranslated || $missing;
            }
        }

        return [$data, $untranslated];
    }

    public function englishCoverage(array $data): array
    {
        $pairs = [];
        foreach (static::TEXT_FIELDS as $field) {
            $pairs[] = [$data[$field] ?? null, $data[$field.'_en'] ?? null];
        }
        foreach (static::LIST_FIELDS as $field) {
            foreach ($data[$field] ?? [] as $i => $item) {
                $pairs[] = [$item, $data[$field.'_en'][$i] ?? null];
            }
        }
        // Рахуються лише фрагменти, де є український текст
        $pairs = array_filter($pairs, fn ($pair) => filled($pair[0]));

        return [count(array_filter($pairs, fn ($pair) => filled($pair[1]))), count($pairs)];
    }

    public function forRender(array $block): ?array
    {
        return $block;
    }

    /**
     * Текст для англійської версії: переклад, якщо він є, інакше український оригінал.
     *
     * @return array{0: mixed, 1: bool} [текст, чи лишився неперекладений український текст]
     */
    private static function pick(mixed $uk, mixed $en): array
    {
        return filled($en) ? [$en, false] : [$uk, filled($uk)];
    }

    /** Обрізає пробіли в рядку; інші значення повертає без змін. */
    protected static function trim(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    /** Необовʼязкове поле: порожній рядок стає null, щоб не зберігати порожніх перекладів. */
    protected static function optional(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    /** Прибирає порожні переклади (`*_en` = null), щоб JSON статей без англійської лишався компактним. */
    protected static function withoutEmptyTranslations(array $data): array
    {
        return array_filter($data, fn ($v, $k) => $v !== null || ! str_ends_with($k, '_en'), ARRAY_FILTER_USE_BOTH);
    }
}
