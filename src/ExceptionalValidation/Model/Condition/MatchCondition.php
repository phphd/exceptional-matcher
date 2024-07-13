<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Condition;

use Throwable;

/** @internal */
interface MatchCondition
{
    public function matches(Throwable $exception): bool;
}
