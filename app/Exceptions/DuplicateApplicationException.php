<?php

namespace App\Exceptions;

use RuntimeException;

/** Від цієї адреси вже є активна анкета на цю тварину (FR-04). */
class DuplicateApplicationException extends RuntimeException
{
}
