<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\ValueObject;

use PhPhD\ExceptionalValidation\Model\Condition\MatchCondition;
use Throwable;

final class ThrownExceptions
{
    public function __construct(
        private readonly Throwable $exception,
    ) {
    }

    public function ejectWith(MatchCondition $condition): ?Throwable
    {
        if (!$condition->matches($this->exception)) {
            return null;
        }

        return $this->exception;
    }
}
