<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Matcher;

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
        foreach ($this->aggregate->getExceptionMatchers() as $matcher) {
            if ($matcher->match($reciprocal)) {
                return true;
            }
        }

        return false;
    }
}
