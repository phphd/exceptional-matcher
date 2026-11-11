<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Value;

use Throwable;

/** @api */
interface ValueException extends Throwable
{
    public function getValue(): mixed;
}
