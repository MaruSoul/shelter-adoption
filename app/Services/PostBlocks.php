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
        'video' => 'Відео',
    ];

    /**
     * Текстові поля кожного типу блоку, що мають англійську пару з суфіксом _en.
     * Структура (порядок блоків, зображення, тварини) у статті одна для обох мов.
     */
    public const TEXT_FIELDS = [
        'heading' => ['text'],
        'paragraph' => ['text'],
        'image' => ['alt', 'caption'],
        'quote' => ['text', 'author'],
        'list' => ['items'],
        'animal' => [],
        'video' => ['caption'],
    ];

    /** Порожній блок заданого типу – з нього конструктор починає редагування. */
    public static function blank(string $type): array
    {
        return [
            'type' => $type,
            'data' => match ($type) {
                'heading' => ['text' => '', 'text_en' => '', 'level' => 2],
                'paragraph' => ['text' => '', 'text_en' => ''],
                'image' => ['path' => '', 'alt' => '', 'alt_en' => '', 'caption' => '', 'caption_en' => ''],
                'quote' => ['text' => '', 'text_en' => '', 'author' => '', 'author_en' => ''],
                'list' => ['style' => 'bullet', 'items' => [''], 'items_en' => ['']],
                'animal' => ['animal_id' => null],
                'video' => ['url' => '', 'caption' => '', 'caption_en' => ''],
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

    /**
     * Доповнює збережені блоки порожніми англійськими полями, щоб конструктор
     * міг одразу показати пару «UA | EN» (статті, створені до появи англійської версії).
     */
    public static function withEnglishKeys(array $blocks): array
    {
        return array_map(function (array $block) {
            foreach (self::TEXT_FIELDS[$block['type']] ?? [] as $field) {
                if ($field === 'items') {
                    $items = $block['data']['items'] ?? [];
                    $en = array_values($block['data']['items_en'] ?? []);
                    $block['data']['items_en'] = array_map(fn ($i) => (string) ($en[$i] ?? ''), array_keys($items));
                } else {
                    $block['data'][$field.'_en'] = (string) ($block['data'][$field.'_en'] ?? '');
                    $block['data'][$field] = (string) ($block['data'][$field] ?? '');
                }
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
            $untranslated = false;
            foreach (self::TEXT_FIELDS[$block['type']] ?? [] as $field) {
                if ($field === 'items') {
                    $en = $block['data']['items_en'] ?? [];
                    foreach ($block['data']['items'] ?? [] as $i => $item) {
                        if (filled($en[$i] ?? null)) {
                            $block['data']['items'][$i] = $en[$i];
                        } elseif (filled($item)) {
                            $untranslated = true;
                        }
                    }

                    continue;
                }
                if (filled($block['data'][$field.'_en'] ?? null)) {
                    $block['data'][$field] = $block['data'][$field.'_en'];
                } elseif (filled($block['data'][$field] ?? null)) {
                    $untranslated = true;
                }
            }
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
            foreach (self::TEXT_FIELDS[$block['type'] ?? ''] ?? [] as $field) {
                if ($field === 'items') {
                    foreach ($block['data']['items'] ?? [] as $i => $item) {
                        if (filled($item)) {
                            $total++;
                            $done += (int) filled($block['data']['items_en'][$i] ?? null);
                        }
                    }

                    continue;
                }
                if (filled($block['data'][$field] ?? null)) {
                    $total++;
                    $done += (int) filled($block['data'][$field.'_en'] ?? null);
                }
            }
        }

        return [$done, $total];
    }

    /** Готує блоки до показу: підставляє адреси зображень і моделі тварин. */
    public function forRender(array $blocks): array
    {
        $animalIds = collect($blocks)->where('type', 'animal')->pluck('data.animal_id')->filter()->all();
        $animals = Animal::query()->visible()->whereKey($animalIds)->with(['species', 'locality', 'coverPhoto'])->get()->keyBy('id');

        return collect($blocks)->map(function (array $block) use ($animals) {
            $data = $block['data'] ?? [];

            return match ($block['type'] ?? null) {
                'image' => ['type' => 'image', 'data' => $data + ['url' => Storage::disk('public')->url($data['path'] ?? '')]] + array_intersect_key($block, ['lang' => 1]),
                'animal' => isset($animals[$data['animal_id'] ?? 0]) ? ['type' => 'animal', 'animal' => $animals[$data['animal_id']]] : null,
                'video' => ($video = self::parseVideo($data['url'] ?? '')) ? ['type' => 'video', 'data' => $data + $video] + array_intersect_key($block, ['lang' => 1]) : null,
                default => $block,
            };
        })->filter()->values()->all();
    }

    private function rules(string $type): array
    {
        return match ($type) {
            'heading' => [
                'text' => ['required', 'string', 'max:160'], 'text_en' => ['nullable', 'string', 'max:160'],
                'level' => ['required', 'integer', Rule::in([2, 3])],
            ],
            'paragraph' => ['text' => ['required', 'string', 'max:5000'], 'text_en' => ['nullable', 'string', 'max:5000']],
            'image' => [
                'path' => ['required', 'string', 'max:255', 'starts_with:posts/'],
                'alt' => ['required', 'string', 'max:160'], 'alt_en' => ['nullable', 'string', 'max:160'],
                'caption' => ['nullable', 'string', 'max:200'], 'caption_en' => ['nullable', 'string', 'max:200'],
            ],
            'quote' => [
                'text' => ['required', 'string', 'max:600'], 'text_en' => ['nullable', 'string', 'max:600'],
                'author' => ['nullable', 'string', 'max:100'], 'author_en' => ['nullable', 'string', 'max:100'],
            ],
            'list' => [
                'style' => ['required', Rule::in(['bullet', 'ordered'])],
                'items' => ['required', 'array', 'min:1', 'max:30'],
                'items.*' => ['required', 'string', 'max:300'],
                'items_en' => ['nullable', 'array', 'max:30'],
                'items_en.*' => ['nullable', 'string', 'max:300'],
            ],
            'animal' => ['animal_id' => ['required', 'integer', Rule::exists('animals', 'id')]],
            'video' => [
                'url' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                    if (! self::parseVideo((string) $value)) {
                        $fail('Вставте посилання на відео з YouTube або Vimeo.');
                    }
                }],
                'caption' => ['nullable', 'string', 'max:200'], 'caption_en' => ['nullable', 'string', 'max:200'],
            ],
        };
    }

    /**
     * Розбирає посилання на відео. Вставка будується з ідентифікатора самим сайтом,
     * тому довільний iframe чи скрипт через цей блок потрапити на сторінку не може.
     *
     * @return array{provider: string, video_id: string, embed: string, thumb: ?string}|null
     */
    public static function parseVideo(string $url): ?array
    {
        $url = trim($url);
        $patterns = [
            // youtube.com/watch?v=ID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID, youtube.com/live/ID
            'youtube' => '~^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})(?:[?&#/].*)?$~',
            'vimeo' => '~^https?://(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d{6,12})(?:[?#/].*)?$~',
        ];
        foreach ($patterns as $provider => $pattern) {
            if (preg_match($pattern, $url, $m)) {
                $id = $m[1];

                return $provider === 'youtube'
                    ? ['provider' => 'youtube', 'video_id' => $id, 'embed' => "https://www.youtube-nocookie.com/embed/{$id}?autoplay=1&rel=0", 'thumb' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg"]
                    : ['provider' => 'vimeo', 'video_id' => $id, 'embed' => "https://player.vimeo.com/video/{$id}?autoplay=1&dnt=1", 'thumb' => null];
            }
        }

        return null;
    }

    private function normalize(string $type, array $data): array
    {
        $trim = fn ($v) => is_string($v) ? trim($v) : $v;
        // Порожній переклад не зберігаємо: на сайті тоді показується український текст
        $opt = fn ($v) => (is_string($v) && trim($v) !== '') ? trim($v) : null;

        $clean = match ($type) {
            'heading' => ['text' => $trim($data['text']), 'text_en' => $opt($data['text_en'] ?? null), 'level' => (int) $data['level']],
            'paragraph' => ['text' => $trim($data['text']), 'text_en' => $opt($data['text_en'] ?? null)],
            'image' => [
                'path' => $data['path'], 'alt' => $trim($data['alt']), 'alt_en' => $opt($data['alt_en'] ?? null),
                'caption' => $opt($data['caption'] ?? null), 'caption_en' => $opt($data['caption_en'] ?? null),
            ],
            'quote' => [
                'text' => $trim($data['text']), 'text_en' => $opt($data['text_en'] ?? null),
                'author' => $opt($data['author'] ?? null), 'author_en' => $opt($data['author_en'] ?? null),
            ],
            'list' => (function () use ($data, $trim, $opt) {
                $items = array_values(array_map($trim, $data['items']));
                $en = array_values($data['items_en'] ?? []);
                // Англійський пункт завжди стоїть під тим самим номером, що й український
                $itemsEn = array_map(fn ($i) => $opt($en[$i] ?? null) ?? '', array_keys($items));

                return ['style' => $data['style'], 'items' => $items, 'items_en' => array_filter($itemsEn) ? $itemsEn : null];
            })(),
            'animal' => ['animal_id' => (int) $data['animal_id']],
            'video' => ['url' => $trim($data['url']), 'caption' => $opt($data['caption'] ?? null), 'caption_en' => $opt($data['caption_en'] ?? null)],
        };

        // Порожні переклади не зберігаємо, щоб JSON статей без англійської лишався таким, як був
        return array_filter($clean, fn ($v, $k) => $v !== null || ! str_ends_with($k, '_en'), ARRAY_FILTER_USE_BOTH);
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
            'url' => 'посилання на відео', 'text_en' => 'текст (EN)', 'alt_en' => 'опис зображення (EN)', 'caption_en' => 'підпис (EN)', 'author_en' => 'автор (EN)',
            'items_en.*' => 'пункт (EN)'];
    }
}
