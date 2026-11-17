<?php

namespace App\Enums;

enum AnimalStatus: string
{
    case Moderation = 'moderation'; // картка волонтера чекає на перевірку
    case Available = 'available';   // шукає дім
    case Adopted = 'adopted';       // знайшла дім
    case Rejected = 'rejected';     // відхилена модератором
    case Archived = 'archived';     // прихована адміністратором

    public function label(): string
    {
        return match ($this) {
            self::Moderation => __('На модерації'),
            self::Available => __('Шукає дім'),
            self::Adopted => __('Знайшла дім'),
            self::Rejected => __('Відхилена'),
            self::Archived => __('В архіві'),
        };
    }

    /** Статуси, у яких тварину видно в публічному каталозі. */
    public static function publicValues(): array
    {
        return [self::Available->value, self::Adopted->value];
    }

    public function isPublic(): bool
    {
        return in_array($this->value, self::publicValues(), true);
    }
}
