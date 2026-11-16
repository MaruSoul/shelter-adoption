<?php

namespace App\Enums;

enum ContactChannel: string
{
    case Phone = 'phone';
    case Telegram = 'telegram';
    case Viber = 'viber';

    public function label(): string
    {
        return match ($this) {
            self::Phone => 'Телефон',
            self::Telegram => 'Telegram',
            self::Viber => 'Viber',
        };
    }
}
