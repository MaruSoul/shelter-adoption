<?php

namespace App\Exceptions;

use App\Enums\ApplicationStatus;
use RuntimeException;

/** Спроба перевести анкету в статус, якого немає в матриці переходів (FR-07). */
class InvalidStatusTransitionException extends RuntimeException
{
    public static function between(ApplicationStatus $from, ApplicationStatus $to): self
    {
        return new self(sprintf('Неможливо змінити статус «%s» на «%s».', $from->label(), $to->label()));
    }
}
