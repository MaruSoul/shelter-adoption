<?php

namespace App\Models;

use App\Enums\AgeGroup;
use App\Enums\AnimalSource;
use App\Enums\AnimalStatus;
use App\Enums\ApplicationStatus;
use App\Enums\Sex;
use App\Support\Ukrainian;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'species_id', 'breed_id', 'color_id', 'locality_id', 'name', 'sex', 'birth_date',
    'is_sterilized', 'is_vaccinated', 'story', 'behavior', 'adoption_terms', 'current_location',
])]
class Animal extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'sex' => Sex::class,
            'source' => AnimalSource::class,
            'status' => AnimalStatus::class,
            'birth_date' => 'immutable_date',
            'is_sterilized' => 'boolean',
            'is_vaccinated' => 'boolean',
            'published_at' => 'datetime',
            'adopted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Людська адреса сторінки: /tvaryny/lira-42
        static::created(function (Animal $animal) {
            $animal->slug = Str::slug(Str::ascii($animal->name)).'-'.$animal->id;
            $animal->saveQuietly();
        });

        static::creating(function (Animal $animal) {
            $animal->slug ??= Str::uuid()->toString();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ----- Звʼязки ----- */

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AnimalPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverPhoto(): HasOne
    {
        return $this->hasOne(AnimalPhoto::class)->ofMany(['sort_order' => 'min', 'id' => 'min']);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    /* ----- Стани ----- */

    public function isPublic(): bool
    {
        return $this->status->isPublic();
    }

    public function acceptsApplications(): bool
    {
        return $this->status === AnimalStatus::Available;
    }

    public function isFromVolunteer(): bool
    {
        return $this->source === AnimalSource::Volunteer;
    }

    /** «Ліра вже знайшла дім» / «Граф вже знайшов дім». */
    public function foundHomePhrase(): string
    {
        return $this->sex === Sex::Female
            ? __(':name вже знайшла дім', ['name' => $this->name])
            : __(':name вже знайшов дім', ['name' => $this->name]);
    }

    public function ageLabel(): string
    {
        return Ukrainian::age($this->birth_date);
    }

    public function activeApplicationsCount(): int
    {
        return $this->applications()
            ->whereIn('status', array_map(fn ($s) => $s->value, ApplicationStatus::active()))
            ->count();
    }

    /* ----- Запити каталогу ----- */

    /** Тварини, яких видно в публічному каталозі. */
    public function scopeVisible(Builder $query): void
    {
        $query->whereIn('status', AnimalStatus::publicValues());
    }

    /**
     * Застосовує фільтри каталогу (FR-01). Кожен фільтр необовʼязковий;
     * всередині однієї групи значення обʼєднуються через АБО, між групами – через І.
     *
     * @param  array<string, mixed>  $f
     */
    public function scopeFilter(Builder $query, array $f): void
    {
        $query->when($f['species'] ?? null, fn ($q, $slug) => $q->whereHas('species', fn ($s) => $s->where('slug', $slug)));

        $query->when($f['sex'] ?? [], fn ($q, $sex) => $q->whereIn('sex', (array) $sex));

        $query->when($f['age'] ?? [], function ($q, $groups) {
            $q->where(function ($q) use ($groups) {
                foreach ((array) $groups as $value) {
                    $group = AgeGroup::tryFrom($value);
                    if (! $group) {
                        continue;
                    }
                    [$from, $to] = $group->birthDateRange();
                    $q->orWhere(function ($q) use ($from, $to) {
                        if ($from) {
                            $q->whereDate('birth_date', '>=', $from);
                        }
                        $q->whereDate('birth_date', '<=', $to);
                    });
                }
            });
        });

        $query->when($f['sterilized'] ?? false, fn ($q) => $q->where('is_sterilized', true));
        $query->when($f['vaccinated'] ?? false, fn ($q) => $q->where('is_vaccinated', true));
        $query->when($f['breed'] ?? null, fn ($q, $id) => $q->where('breed_id', $id));
        $query->when($f['color'] ?? null, fn ($q, $id) => $q->where('color_id', $id));
        $query->when($f['locality'] ?? null, fn ($q, $id) => $q->where('locality_id', $id));
        $query->when($f['region'] ?? null, fn ($q, $id) => $q->whereHas('locality', fn ($l) => $l->where('region_id', $id)));

        // За замовчуванням каталог показує лише тих, хто ще шукає дім
        if (empty($f['adopted'])) {
            $query->where('status', AnimalStatus::Available->value);
        } else {
            $query->where('status', AnimalStatus::Adopted->value);
        }
    }
}
