<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Безпечна мінірозмітка для текстів, які пише адміністратор (абзаци статей, SEO-тексти сторінок).
 *
 * Спершу весь текст екранується, тому HTML і скрипти не виконуються ніколи (захист від XSS).
 * Потім розпізнається лише невеликий набір позначок:
 *   [текст посилання](/tvaryny) або [текст](https://...)  – посилання;
 *   **жирний**                                            – виділення;
 *   ## Заголовок, ### Підзаголовок                        – заголовки (лише в block());
 *   - пункт                                               – маркований список (лише в block()).
 * Такий формат легко писати вручну, і його так само розуміє ШІ, якщо попросити підготувати текст.
 */
class SimpleMarkup
{
    /** Підказка для форм адмінки. */
    public const HINT = 'Посилання: [текст](/tvaryny) · жирний: **текст**';

    public const BLOCK_HINT = 'Посилання: [текст](/tvaryny) · жирний: **текст** · заголовок: ## Текст · список: - пункт · абзаци – через порожній рядок';

    /** Рядок тексту з посиланнями й жирним (для абзаців, пунктів списку). */
    public static function inline(?string $text): HtmlString
    {
        return new HtmlString(self::inlineHtml((string) $text));
    }

    /** Кілька абзаців із заголовками й списками (для SEO-текстів сторінок). */
    public static function block(?string $text): HtmlString
    {
        $html = [];
        $paragraphs = preg_split('/\R{2,}/u', trim((string) $text)) ?: [];
        foreach ($paragraphs as $paragraph) {
            $lines = preg_split('/\R/u', trim($paragraph));
            if (preg_match('/^(#{2,3})\s+(.+)$/u', $lines[0], $m) && count($lines) === 1) {
                $tag = strlen($m[1]) === 2 ? 'h2' : 'h3';
                $html[] = "<{$tag}>".self::inlineHtml($m[2])."</{$tag}>";
            } elseif (collect($lines)->every(fn ($l) => preg_match('/^\s*[-–•]\s+/u', $l))) {
                $items = array_map(fn ($l) => '<li>'.self::inlineHtml(preg_replace('/^\s*[-–•]\s+/u', '', $l)).'</li>', $lines);
                $html[] = '<ul>'.implode('', $items).'</ul>';
            } elseif ($paragraph !== '') {
                $html[] = '<p>'.implode('<br>', array_map(self::inlineHtml(...), $lines)).'</p>';
            }
        }

        return new HtmlString(implode("\n", $html));
    }

    /** Текст без позначок – для мета-описів і підрахунку часу читання. */
    public static function plain(?string $text): string
    {
        $text = preg_replace('/\[([^\]]+)\]\([^)\s]+\)/u', '$1', (string) $text);

        return trim(preg_replace(['/\*\*(.+?)\*\*/u', '/^#{2,3}\s+/mu', '/^\s*[-–•]\s+/mu'], ['$1', '', ''], $text));
    }

    private static function inlineHtml(string $text): string
    {
        $escaped = e($text);

        // Посилання: лише адреси сайту (/...) або http(s); javascript: та інші схеми не розпізнаються.
        // «//домен» – це адреса іншого сайту без схеми, тому як внутрішнє посилання її не приймаємо.
        $escaped = preg_replace_callback('~\[([^\]\n]{1,200})\]\(((?:https?://|/(?!/))[^\s)]{0,500})\)~u', function (array $m) {
            $href = $m[2];
            $external = str_starts_with($href, 'http') && ! str_starts_with($href, rtrim(config('app.url'), '/'));

            return '<a href="'.$href.'"'.($external ? ' rel="noopener" target="_blank"' : '').'>'.$m[1].'</a>';
        }, $escaped);

        return preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $escaped);
    }
}
