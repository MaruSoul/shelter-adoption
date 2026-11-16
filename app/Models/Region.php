<?php

namespace App\Models;

use App\Models\Concerns\SortsUkrainian;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Region extends Model
{
    use SortsUkrainian;

    public function localities(): HasMany
    {
        return $this->hasMany(Locality::class);
    }
}
