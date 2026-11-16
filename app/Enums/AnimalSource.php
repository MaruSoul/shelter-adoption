<?php

namespace App\Enums;

enum AnimalSource: string
{
    case Shelter = 'shelter';
    case Volunteer = 'volunteer';

    public function label(): string
    {
        return match ($this) {
            self::Shelter => 'Притулок',
            self::Volunteer => 'Розміщено волонтером',
        };
    }
}
