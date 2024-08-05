<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Condition\Exception;

use Throwable;

/** @api */
interface InvalidValueException extends Throwable
{
    public function getInvalidValue(): mixed;
}
