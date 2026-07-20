<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher;

use Iterator;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan as CatchPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMatchingRuleSet;
use Throwable;

final class CatchAttributesExceptionMatcherAggregate implements ExceptionMatcherAggregate
{
    public function __construct(
        private readonly PropertyMatchingRuleSet $propertyRuleSet,
        /** @var iterable<CatchPlan<Throwable>> */
        private readonly iterable $catchPlans,
    ) {
    }

    public function getExceptionMatchers(): Iterator
    {
        foreach ($this->catchPlans as $catchPlan) {
            yield $catchPlan->bind($this->propertyRuleSet);
        }
    }
}
