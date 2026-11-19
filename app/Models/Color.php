<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'name_en'])]
class Color extends Model
{
    use HasLocalizedName;

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }
}
