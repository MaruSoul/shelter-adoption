<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Користувач',
            self::Admin => 'Адміністратор притулку',
        };
    }
}
