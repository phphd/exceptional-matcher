<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use Throwable;

/** @api */
interface ValueException extends Throwable
{
    public function getValue(): mixed;
}
