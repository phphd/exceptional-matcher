<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

/** @api */
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
