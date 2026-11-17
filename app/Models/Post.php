<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Models\Concerns\HasTranslations;
use App\Services\PostBlocks;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['post_category_id', 'title', 'title_en', 'slug', 'excerpt', 'excerpt_en', 'cover_path', 'blocks'])]
class Post extends Model
{
    use HasFactory, HasTranslations;

    /** Поля статті з англійською парою; текст блоків перекладається всередині JSON (див. PostBlocks). */
    public const TRANSLATABLE = ['title', 'excerpt'];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published && $this->published_at?->isPast();
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    /**
     * Орієнтовний час читання в хвилинах: слова з усіх текстових блоків ÷ 180 слів/хв
     * (середня швидкість читання українського тексту з екрана), щонайменше 1 хвилина.
     */
    public function readingMinutes(): int
    {
        $text = collect($this->localizedBlocks())
            ->map(fn (array $b) => implode(' ', array_filter([
                $b['data']['text'] ?? null,
                $b['data']['author'] ?? null,
                $b['data']['caption'] ?? null,
                ...($b['data']['items'] ?? []),
            ], 'is_string')))
            ->implode(' ');
        $words = preg_match_all('/[\p{L}\p{N}ʼ\'-]+/u', $text);

        return max(1, (int) round($words / 180));
    }

    /** Блоки з текстом мовою відвідувача (неперекладені – українською з позначкою lang). */
    public function localizedBlocks(?string $locale = null): array
    {
        return PostBlocks::localize($this->blocks ?? [], $locale ?? app()->getLocale());
    }

    /**
     * Наскільки стаття перекладена англійською: заголовок, анонс і всі текстові фрагменти блоків.
     *
     * @return array{0: int, 1: int} [перекладено, усього]
     */
    public function englishCoverage(): array
    {
        [$done, $total] = PostBlocks::englishCoverage($this->blocks ?? []);
        foreach (self::TRANSLATABLE as $field) {
            if (filled($this->getAttribute($field))) {
                $total++;
                $done += (int) filled($this->getAttribute($field.'_en'));
            }
        }

        return [$done, $total];
    }

    /** full – перекладено все, partial – частину, none – нічого. */
    public function englishState(): string
    {
        [$done, $total] = $this->englishCoverage();

        return match (true) {
            $done === 0 => 'none',
            $done >= $total => 'full',
            default => 'partial',
        };
    }
}
