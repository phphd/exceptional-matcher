<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Throwable;

/**
 * @api - use exception_value constant for a class name instead
 *
 * @implements MatchCondition<ValueException>
 */
final class ExceptionValueMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly mixed $propertyValue,
    ) {
    }

    /** @param ValueException $exception */
    public function matches(Throwable $exception): bool
    {
        return $exception->getValue() === $this->propertyValue;
    }
}
