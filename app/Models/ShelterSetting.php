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
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->first() ?? new static([
                'name' => config('app.name'),
                'notification_email' => config('mail.from.address'),
            ]);
        });
    }
}
