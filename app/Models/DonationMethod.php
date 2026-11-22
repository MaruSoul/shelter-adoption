<?php

namespace App\Models;

use App\Enums\DonationType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Спосіб фінансової допомоги притулку: банка Monobank, реквізити, PayPal чи інше посилання.
 * Набір полів у `details` залежить від типу (App\Enums\DonationType).
 */
#[Fillable(['type', 'position', 'is_visible', 'title', 'title_en', 'note', 'note_en', 'details'])]
class DonationMethod extends Model
{
    use HasTranslations;

    public const TRANSLATABLE = ['title', 'note'];

    protected function casts(): array
    {
        return [
            'type' => DonationType::class,
            'is_visible' => 'boolean',
            'details' => 'array',
        ];
    }

    /** Способи, які бачать відвідувачі, у порядку з панелі. */
    public function scopeShown(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('position')->orderBy('id');
    }

    /** Назва вкладки мовою відвідувача; без власної – типова назва способу. */
    public function tabTitle(): string
    {
        return $this->tr('title') ?: $this->type->defaultTitle();
    }

    public function detail(string $key): ?string
    {
        $value = $this->details[$key] ?? null;

        return filled($value) ? (string) $value : null;
    }
}
