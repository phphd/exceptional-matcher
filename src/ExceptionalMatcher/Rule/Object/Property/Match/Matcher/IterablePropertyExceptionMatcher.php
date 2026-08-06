<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable\ItemOfIterableExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\PropertyExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregate;

use function is_object;

/** @internal */
final class IterablePropertyExceptionMatcher implements ExceptionMatchingRuleAggregate
{
    public function __construct(
        private readonly PropertyExceptionMappingNode $propertyRuleSet,
        /** @var ObjectExceptionMappingPlanRegistry<object> */
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
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
