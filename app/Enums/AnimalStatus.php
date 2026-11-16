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
            self::Moderation => 'На модерації',
            self::Available => 'Шукає дім',
            self::Adopted => 'Знайшла дім',
            self::Rejected => 'Відхилена',
            self::Archived => 'В архіві',
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
