<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Condition;

use PhPhD\ExceptionalValidation\Model\Condition\Exception\ValueException;
use Throwable;

final class ValueExceptionMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly mixed $value,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        if (!$exception instanceof ValueException) {
            return false;
        }

        return $exception->getValue() === $this->value;
    }
}
