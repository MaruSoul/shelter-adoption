<?php

namespace App\Models;

use App\Models\Concerns\SortsUkrainian;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['region_id', 'name'])]
class Locality extends Model
{
    use SortsUkrainian;

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
