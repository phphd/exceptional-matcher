<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\PropertyExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregate;
use Throwable;

final class CatchAttributesExceptionMatcherAggregate implements ExceptionMatchingRuleAggregate
{
    public function __construct(
        private readonly PropertyExceptionMappingNode $propertyRuleSet,
        /** @var iterable<CatchExceptionMappingPlan<Throwable>> */
        private readonly iterable $catchPlans,
    ) {
    }

    public function getExceptionMatchingRules(): Iterator
    {
        foreach ($this->catchPlans as $catchPlan) {
            yield $catchPlan->bind($this->propertyRuleSet);
        }
    }
}
