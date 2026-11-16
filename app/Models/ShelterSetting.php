<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Налаштування притулку зберігаються одним рядком і кешуються (FR-16).
 */
#[Fillable(['name', 'tagline', 'address', 'phone', 'email', 'notification_email', 'instagram_url', 'telegram_url'])]
class ShelterSetting extends Model
{
    private const CACHE_KEY = 'shelter_settings';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        // Кеш зберігає масив атрибутів, а не обʼєкт: Laravel 13 за замовчуванням не десеріалізує обʼєкти з кешу
        $attributes = Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->first()?->getAttributes() ?? [
                'name' => config('app.name'),
                'notification_email' => config('mail.from.address'),
            ];
        });

        return (new static)->newFromBuilder($attributes);
    }
}
