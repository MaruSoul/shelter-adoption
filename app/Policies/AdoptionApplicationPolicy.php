<?php

namespace App\Policies;

use App\Models\AdoptionApplication;
use App\Models\User;

class AdoptionApplicationPolicy
{
    /** Анкету бачать адміністратор і автор, який підтвердив свою пошту. */
    public function view(User $user, AdoptionApplication $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasVerifiedEmail() && $application->user_id === $user->id;
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
