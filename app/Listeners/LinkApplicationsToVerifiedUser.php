<?php

namespace App\Listeners;

use App\Models\AdoptionApplication;
use Illuminate\Auth\Events\Verified;

/**
 * Після підтвердження e-mail привʼязує до акаунта анкети, подані раніше з тією ж адресою (FR-09).
 * До підтвердження анкети в кабінеті не показуються – інакше чужу пошту можна було б «присвоїти».
 */
class LinkApplicationsToVerifiedUser
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        AdoptionApplication::query()
            ->whereNull('user_id')
            ->where('email', mb_strtolower($user->getEmailForVerification()))
            ->update(['user_id' => $user->getKey()]);
    }
}
