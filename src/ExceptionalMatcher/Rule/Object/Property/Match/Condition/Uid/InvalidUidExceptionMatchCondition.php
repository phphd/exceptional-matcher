<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Throwable;

/**
 * @internal - use {@see uid_value} constant for a class reference instead
 *
 * @implements MatchCondition<InvalidUidException>
 */
final class InvalidUidExceptionMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly string $propertyValue,
    ) {
    }

    /** @param InvalidUidException $exception */
    public function matches(Throwable $exception): bool
    {
        return $exception->invalidValue === $this->propertyValue;
    }
}
