<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher;

use Iterator;
use PhPhD\ExceptionalMatcher\Rule\ItemOfIterableExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyExceptionMappingNodeSet;

/** @internal */
final class IterablePropertyExceptionMatcher implements ExceptionMatchingRuleAggregate
{
    public function __construct(
        private readonly PropertyExceptionMappingNodeSet $propertyRuleSet,
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

            yield new ItemOfIterableExceptionMappingNode($this->propertyRuleSet, $key, $item, $itemPlan);
        }
    }
}
