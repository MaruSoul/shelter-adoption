<?php

namespace App\Enums;

enum AnimalSource: string
{
    case Shelter = 'shelter';
    case Volunteer = 'volunteer';

    public function label(): string
    {
        return match ($this) {
            self::Shelter => __('Притулок'),
            self::Volunteer => __('Розміщено волонтером'),
        };
    }
}
