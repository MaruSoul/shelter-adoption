<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['post_category_id', 'title', 'slug', 'excerpt', 'cover_path', 'blocks'])]
class Post extends Model
{
    use HasFactory;

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
        $text = collect($this->blocks ?? [])
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
}
