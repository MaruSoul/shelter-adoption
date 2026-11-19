<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * SEO-налаштування сторінки: Title, Description, H1 і текст унизу сторінки (обидві мови).
 * Порожнє поле означає «як зараз»: сторінка показує свій типовий заголовок і опис.
 */
#[Fillable(['key', 'title', 'title_en', 'description', 'description_en', 'h1', 'h1_en', 'text', 'text_en'])]
class SeoPage extends Model
{
    use HasTranslations;

    /**
     * На відміну від інших двомовних полів, SEO-тексти не підміняються іншою мовою:
     * український Title на англійській сторінці гірший за типовий англійський заголовок.
     */
    public function tr(string $field, ?string $locale = null): ?string
    {
        $value = $this->getAttribute(($locale ?? app()->getLocale()) === 'en' ? $field.'_en' : $field);

        return filled($value) ? $value : null;
    }

    public function isShownInLocale(string $field, ?string $locale = null): bool
    {
        return true;
    }

    public const TRANSLATABLE = ['title', 'description', 'h1', 'text'];

    private const CACHE_KEY = 'seo_pages';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** Сторінки, для яких можна задати SEO: ключ → назва в адмінці (розділи видів додаються автоматично). */
    public static function pages(): array
    {
        $pages = ['home' => 'Головна', 'animals' => 'Каталог «Знайти друга»'];
        foreach (Species::orderBy('id')->get() as $species) {
            $pages['species:'.$species->slug] = 'Каталог: '.$species->name_plural;
        }

        return $pages + ['blog' => 'Блог'];
    }

    /** Налаштування сторінки або порожня модель (усі поля null), якщо їх ще не задано. */
    public static function for(string $key): self
    {
        $all = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->get()->keyBy('key')->map->getAttributes()->all());

        return isset($all[$key]) ? (new static)->newFromBuilder($all[$key]) : new static(['key' => $key]);
    }
}
