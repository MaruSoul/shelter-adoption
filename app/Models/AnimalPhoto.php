<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['animal_id', 'path', 'thumb_path', 'alt', 'sort_order'])]
class AnimalPhoto extends Model
{
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /** Мініатюра, а поки вона генерується – оригінал. */
    public function thumbUrl(): string
    {
        return Storage::disk('public')->url($this->thumb_path ?? $this->path);
    }
}
