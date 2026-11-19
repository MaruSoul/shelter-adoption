<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['species_id', 'name', 'name_en'])]
class Breed extends Model
{
    use HasLocalizedName;

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }
}
