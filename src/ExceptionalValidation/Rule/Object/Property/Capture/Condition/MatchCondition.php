<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use Throwable;

/** @internal */
interface MatchCondition
{
    public function matches(Throwable $exception): bool;
}
