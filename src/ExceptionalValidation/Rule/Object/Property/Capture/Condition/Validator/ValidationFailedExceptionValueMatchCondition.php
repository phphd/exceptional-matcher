<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator;

use LogicException;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/** @api */
final class ValidationFailedExceptionValueMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly mixed $value,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        if (!$exception instanceof ValidationFailedException) {
            throw new LogicException('ValidationFailedExceptionValueMatchCondition can only be used for ValidationFailedException');
        }

        return $exception->getValue() === $this->value;
    }
}
