<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Condition\Exception;

use Throwable;

/** @api */
interface ValueException extends Throwable
{
    public function getValue(): mixed;
}
