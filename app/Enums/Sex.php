<?php

namespace App\Enums;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Хлопчик',
            self::Female => 'Дівчинка',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::Male => 'Хлопчики',
            self::Female => 'Дівчатка',
        };
    }
}
