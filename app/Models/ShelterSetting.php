<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Налаштування притулку зберігаються одним рядком і кешуються (FR-16).
 */
#[Fillable(['name', 'tagline', 'address', 'phone', 'email', 'notification_email', 'instagram_url', 'facebook_url', 'telegram_url', 'tiktok_url', 'youtube_url'])]
class ShelterSetting extends Model
{
    private const CACHE_KEY = 'shelter_settings';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** Мережі для блоку «Слідкуй за нами»: поле → назва. Порядок тут = порядок кнопок. */
    public const SOCIALS = [
        'instagram_url' => 'Instagram',
        'facebook_url' => 'Facebook',
        'telegram_url' => 'Telegram',
        'tiktok_url' => 'TikTok',
        'youtube_url' => 'YouTube',
    ];

    /** @return array<string, array{label: string, url: string}> заповнені посилання, ключ – коротка назва мережі */
    public function socialLinks(): array
    {
        $links = [];
        foreach (self::SOCIALS as $field => $label) {
            if (filled($this->{$field})) {
                $links[str_replace('_url', '', $field)] = ['label' => $label, 'url' => $this->{$field}];
            }
        }

        return $links;
    }

    public static function current(): self
    {
        // Кеш зберігає масив атрибутів, а не обʼєкт: Laravel 13 за замовчуванням не десеріалізує обʼєкти з кешу
        $attributes = Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->first()?->getAttributes() ?? [
                // Налаштувань ще немає (свіжа інсталяція): усі поля порожні, крім назви та адреси сповіщень
                ...array_fill_keys((new static)->getFillable(), null),
                'name' => config('app.name'),
                'notification_email' => config('mail.from.address'),
            ];
        });

        // Кеш міг лишитися з версії до нових полів (наприклад, соцмереж): доповнюємо відсутні ключі,
        // щоб строгий режим не падав на MissingAttributeException
        $attributes = [...array_fill_keys((new static)->getFillable(), null), ...$attributes];

        return (new static)->newFromBuilder($attributes);
    }
}
