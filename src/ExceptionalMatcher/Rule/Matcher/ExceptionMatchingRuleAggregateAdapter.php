<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Matcher;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;

/** @internal */
final class ExceptionMatchingRuleAggregateAdapter implements ExceptionMatchingRule
{
    public function __construct(
        private readonly ExceptionMatchingRuleAggregate $aggregate,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->aggregate->getExceptionMatchingRules() as $rule) {
            if ($rule->match($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getAggregate(): ExceptionMatchingRuleAggregate
    {
        return $this->aggregate;
    }
}
