<?php

namespace App\Services\Blocks;

/**
 * Відео з YouTube або Vimeo. Зберігається лише посилання; адресу вставки сайт будує сам з ідентифікатора,
 * тому довільний iframe чи скрипт через цей блок на сторінку потрапити не може.
 * Для англійської версії можна вказати окреме відео (url_en); його відсутність не вважається неперекладеним текстом.
 */
class VideoBlock extends AbstractBlock
{
    protected const TEXT_FIELDS = ['caption'];

    private const PATTERNS = [
        // youtube.com/watch?v=ID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID, youtube.com/live/ID
        'youtube' => '~^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})(?:[?&#/].*)?$~',
        'vimeo' => '~^https?://(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d{6,12})(?:[?#/].*)?$~',
    ];

    public function key(): string
    {
        return 'video';
    }

    public function label(): string
    {
        return 'Відео';
    }

    public function blank(): array
    {
        return ['url' => '', 'url_en' => '', 'caption' => '', 'caption_en' => ''];
    }

    public function rules(): array
    {
        $videoUrl = function (string $attribute, mixed $value, \Closure $fail) {
            if (! self::parse((string) $value)) {
                $fail('Вставте посилання на відео з YouTube або Vimeo.');
            }
        };

        return [
            'url' => ['required', 'string', 'max:255', $videoUrl],
            'url_en' => ['nullable', 'string', 'max:255', $videoUrl],
            'caption' => ['nullable', 'string', 'max:200'],
            'caption_en' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function normalize(array $data): array
    {
        return self::withoutEmptyTranslations([
            'url' => self::trim($data['url']),
            'url_en' => self::optional($data['url_en'] ?? null),
            'caption' => self::optional($data['caption'] ?? null),
            'caption_en' => self::optional($data['caption_en'] ?? null),
        ]);
    }

    public function isFilled(array $data): bool
    {
        return self::parse((string) ($data['url'] ?? '')) !== null;
    }

    public function withEnglishKeys(array $data): array
    {
        return parent::withEnglishKeys($data) + ['url_en' => ''];
    }

    public function localize(array $data): array
    {
        [$data, $untranslated] = parent::localize($data);
        if (filled($data['url_en'] ?? null)) {
            $data['url'] = $data['url_en'];
        }

        return [$data, $untranslated];
    }

    public function forRender(array $block): ?array
    {
        $video = self::parse($block['data']['url'] ?? '');
        if (! $video) {
            return null;
        }
        $block['data'] += $video;

        return $block;
    }

    /**
     * Розбирає посилання на відео.
     *
     * @return array{provider: string, video_id: string, embed: string, thumb: ?string}|null
     */
    public static function parse(string $url): ?array
    {
        foreach (self::PATTERNS as $provider => $pattern) {
            if (preg_match($pattern, trim($url), $m)) {
                $id = $m[1];

                return $provider === 'youtube'
                    ? ['provider' => 'youtube', 'video_id' => $id, 'embed' => "https://www.youtube-nocookie.com/embed/{$id}?autoplay=1&rel=0", 'thumb' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg"]
                    : ['provider' => 'vimeo', 'video_id' => $id, 'embed' => "https://player.vimeo.com/video/{$id}?autoplay=1&dnt=1", 'thumb' => null];
            }
        }

        return null;
    }
}
