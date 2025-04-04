<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/** @api */
final class ExceptionValueMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly mixed $value,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        if ($exception instanceof ValueException) {
            return $exception->getValue() === $this->value;
        }

        if ($exception instanceof ValidationFailedException) {
            return $exception->getValue() === $this->value;
        }

        return false;
    }
}
