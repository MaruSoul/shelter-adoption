<?php

namespace App\Enums;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => __('Хлопчик'),
            self::Female => __('Дівчинка'),
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::Male => __('Хлопчики'),
            self::Female => __('Дівчатка'),
        };
    }
}
