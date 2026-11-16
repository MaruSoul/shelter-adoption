<?php

namespace App\Policies;

use App\Enums\AnimalStatus;
use App\Models\Animal;
use App\Models\User;

/**
 * Права доступу до карток тварин (NFR-01). Перевіряються на сервері.
 */
class AnimalPolicy
{
    /** Перегляд картки, якої ще немає в публічному каталозі. */
    public function preview(User $user, Animal $animal): bool
    {
        return $user->isAdmin() || $animal->created_by === $user->id;
    }

    /** Волонтер редагує лише власну картку і лише поки її не прилаштовано. */
    public function update(User $user, Animal $animal): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $animal->created_by === $user->id
            && in_array($animal->status, [AnimalStatus::Moderation, AnimalStatus::Available, AnimalStatus::Rejected], true);
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
