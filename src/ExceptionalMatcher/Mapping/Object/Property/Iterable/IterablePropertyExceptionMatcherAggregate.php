<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\PropertyExceptionMappingNode;

use function is_object;

/** @internal */
final class IterablePropertyExceptionMatcherAggregate implements ExceptionMatcherAggregate
{
    public function __construct(
        private readonly PropertyExceptionMappingNode $property,
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $planRegistry,
    ) {
    }

    public function getExceptionMatchers(): Iterator
    {
        /** @var iterable<array-key,mixed> $value */
        $value = $this->property->getValue();

        foreach ($value as $key => $item) {
            if (!is_object($item)) {
                continue;
            }

            $itemPlan = $this->planRegistry->getMappingBlueprint($item::class);

            if (null === $itemPlan) {
                continue;
            }

            yield new ItemOfIterableExceptionMappingNode($this->property, $key, $item, $itemPlan);
        }
    }
}
