<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher;

use Iterator;
use PhPhD\ExceptionalMatcher\Rule\ItemOfIterableMappingRule;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingRuleSet;

/** @internal */
final class IterablePropertyExceptionMatcher implements ExceptionMatchingRuleAggregate
{
    public function __construct(
        private readonly PropertyMappingRuleSet $propertyRuleSet,
        private readonly ClassMatchingPlanRegistry $planRegistry,
    ) {
    }

    public function getExceptionMatchingRules(): Iterator
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

            yield new ItemOfIterableMappingRule($this->propertyRuleSet, $key, $item, $itemPlan);
        }
    }
}
