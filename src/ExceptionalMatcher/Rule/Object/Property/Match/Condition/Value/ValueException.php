<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value;

use Throwable;

/** @api */
interface ValueException extends Throwable
{
    public function getValue(): mixed;
}
