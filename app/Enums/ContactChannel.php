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

    /** Посилання, яке одразу відкриває розмову з людиною в цьому каналі (номер у форматі +380XXXXXXXXX). */
    public function link(string $phone): string
    {
        return match ($this) {
            self::Phone => 'tel:'.$phone,
            self::Telegram => 'https://t.me/'.$phone,
            self::Viber => 'viber://chat?number='.rawurlencode($phone),
        };
    }

    public function actionLabel(): string
    {
        return match ($this) {
            self::Phone => 'Подзвонити',
            self::Telegram => 'Написати в Telegram',
            self::Viber => 'Написати у Viber',
        };
    }
}
