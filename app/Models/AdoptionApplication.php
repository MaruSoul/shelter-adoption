<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ContactChannel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'animal_id', 'user_id', 'region_id', 'name', 'email', 'phone', 'social_url',
    'contact_channels', 'comment', 'consent_at', 'ip_address',
])]
class AdoptionApplication extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'contact_channels' => AsEnumCollection::of(ContactChannel::class),
            'consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // E-mail зберігаємо в нижньому регістрі: так працює і пошук, і привʼязка до акаунта
        static::saving(function (AdoptionApplication $application) {
            $application->email = mb_strtolower(trim($application->email));
        });
    }

    public function animal(): BelongsTo
    {
        // Анкета лишається читабельною, навіть якщо волонтер видалив картку тварини
        return $this->belongsTo(Animal::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(ApplicationStatusChange::class)->orderBy('created_at')->orderBy('id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', array_map(fn ($s) => $s->value, ApplicationStatus::active()));
    }

    /** Пошук адміністратора за імʼям, телефоном або e-mail (FR-06). */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);
        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
        $digits = preg_replace('/\D+/', '', $term);

        $query->where(function ($q) use ($like, $digits) {
            $q->where('name', 'ilike', $like)->orWhere('email', 'ilike', $like);
            if ($digits !== '') {
                $q->orWhere('phone', 'like', '%'.$digits.'%');
            }
        });
    }
}
