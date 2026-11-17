<?php

namespace App\Enums;

/**
 * Статуси анкети на прилаштування та матриця дозволених переходів між ними.
 */
enum ApplicationStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case MeetingScheduled = 'meeting_scheduled';
    case Adopted = 'adopted';
    case Declined = 'declined';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => __('Нова'),
            self::InProgress => __('В роботі'),
            self::MeetingScheduled => __('Знайомство призначено'),
            self::Adopted => __('Прилаштовано'),
            self::Declined => __('Відмовлено'),
            self::Closed => __('Закрито'),
        };
    }

    /**
     * Матриця переходів: з якого статусу в які можна перевести анкету вручну.
     * «Закрито» система ставить автоматично, коли тварину прилаштовано за іншою анкетою.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::InProgress, self::Declined],
            self::InProgress => [self::MeetingScheduled, self::Adopted, self::Declined],
            self::MeetingScheduled => [self::InProgress, self::Adopted, self::Declined],
            self::Adopted, self::Declined, self::Closed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isActive(): bool
    {
        return in_array($this, self::active(), true);
    }

    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    /** @return list<self> */
    public static function active(): array
    {
        return [self::New, self::InProgress, self::MeetingScheduled];
    }

    /** Колір бейджа в інтерфейсі. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-sun-soft text-accent',
            self::InProgress, self::MeetingScheduled => 'bg-surface text-primary',
            self::Adopted => 'bg-success-soft text-success',
            self::Declined => 'bg-danger-soft text-danger',
            self::Closed => 'bg-line text-muted',
        };
    }
}
