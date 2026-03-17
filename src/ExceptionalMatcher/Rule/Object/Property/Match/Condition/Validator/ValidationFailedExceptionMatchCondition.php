<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * @api
 *
 * @implements MatchCondition<ValidationFailedException>
 */
final class ValidationFailedExceptionMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly mixed $propertyValue,
    ) {
    }

    /** @param ValidationFailedException $exception */
    public function matches(Throwable $exception): bool
    {
        return $exception->getValue() === $this->propertyValue;
    }
}
