<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Matcher;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;

/** @internal */
final class ExceptionMatcherAggregateAdapter implements ExceptionMatcher
{
    public function __construct(
        private readonly ExceptionMatcherAggregate $aggregate,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->aggregate->getExceptionMatchers() as $rule) {
            if ($rule->match($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getAggregate(): ExceptionMatcherAggregate
    {
        return $this->aggregate;
    }
}
