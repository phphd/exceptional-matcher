<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Condition;

use Throwable;

interface MatchCondition
{
    public function matches(Throwable $exception): bool;
}
