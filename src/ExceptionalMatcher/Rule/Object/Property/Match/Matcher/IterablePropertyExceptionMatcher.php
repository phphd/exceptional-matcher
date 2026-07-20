<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher;

use Iterator;
use PhPhD\ExceptionalMatcher\Rule\ItemOfIterableMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMatchingRuleSet;

/** @internal */
final class IterablePropertyExceptionMatcher implements ExceptionMatcherAggregate
{
    public function __construct(
        private readonly PropertyMatchingRuleSet $propertyRuleSet,
        private readonly ClassMatchingPlanRegistry $planRegistry,
    ) {
    }

    public function getExceptionMatchers(): Iterator
    {
        /** @var iterable<array-key,mixed> $value */
        $value = $this->propertyRuleSet->getValue();

        foreach ($value as $key => $item) {
            if (!is_object($item)) {
                continue;
            }

            $itemPlan = $this->planRegistry->getPlan($item::class);

            if (null === $itemPlan) {
                continue;
            }

            yield new ItemOfIterableMatchingRule($this->propertyRuleSet, $key, $item, $itemPlan);
        }
    }
}
