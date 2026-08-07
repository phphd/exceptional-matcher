<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Integration\Validator;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\MatchCondition;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * @internal - use {@see validated_value} constant for a class reference instead
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
