<?php

namespace App\Exceptions;

use RuntimeException;

/** Тварину вже прилаштовано за іншою анкетою (FR-08, NFR-02). */
class AnimalAlreadyAdoptedException extends RuntimeException
{
}
