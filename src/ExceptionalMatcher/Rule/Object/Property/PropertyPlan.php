<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property;

use Generator;
use PhPhD\ExceptionalMatcher\Rule\ItemOfIterableMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMatchingRuleSet;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

use function is_iterable;
use function is_object;

/** @internal */
final class PropertyPlan
{
    public function __construct(
        private readonly ReflectionProperty $property,
        /** @var iterable<int,CatchPlan<Throwable>> */
        private readonly iterable $catchPlans,
        private readonly ClassMatchingPlanRegistry $planRegistry,
    ) {
    }

    public function getName(): string
    {
        return $this->property->getName();
    }

    /**
     * @api the seam for the mapping linter: forcing this iterable compiles every `#[Catch_]` of the property
     *
     * @return iterable<int,CatchPlan<Throwable>>
     */
    public function getCatchPlans(): iterable
    {
        return $this->catchPlans;
    }

    /** @psalm-suppress UnusedVariable the by-reference closure capture is the usage */
    public function bind(ObjectMatchingRuleSet $objectRuleSet): MatchingRule
    {
        $value = $this->getPropertyValue($objectRuleSet->getValue());

        /** @var ?PropertyMatchingRuleSet $propertyRuleSet */
        $propertyRuleSet = null;

        // the rules generator runs only once the rule set processes it,
        // by which point the by-reference variable is already assigned
        $rules = (function () use (&$propertyRuleSet, $value): Generator {
            Assert::isInstanceOf($propertyRuleSet, PropertyMatchingRuleSet::class);

            foreach ($this->catchPlans as $catchPlan) {
                yield $catchPlan->bind($propertyRuleSet);
            }

            yield from $this->getNestedObjectRules($value, $propertyRuleSet);

            yield from $this->getIterableItemRules($value, $propertyRuleSet);
        })();

        return $propertyRuleSet = new PropertyMatchingRuleSet($objectRuleSet, $this->getName(), $value, $rules);
    }

    private function getPropertyValue(object $message): mixed
    {
        if (!$this->property->isInitialized($message)) {
            return null;
        }

        return $this->property->getValue($message);
    }

    /** @return Generator<MatchingRule> */
    private function getNestedObjectRules(mixed $value, PropertyMatchingRuleSet $propertyRuleSet): Generator
    {
        if (!is_object($value)) {
            return;
        }

        $nestedPlan = $this->planRegistry->getPlan($value::class);

        if (null === $nestedPlan) {
            return;
        }

        yield $nestedPlan->bind($value, $propertyRuleSet);
    }

    /** @return Generator<MatchingRule> */
    private function getIterableItemRules(mixed $value, PropertyMatchingRuleSet $propertyRuleSet): Generator
    {
        if (!is_iterable($value) || [] === $value) {
            return;
        }

        /** @var iterable<array-key,mixed> $value */
        foreach ($value as $key => $item) {
            if (!is_object($item)) {
                continue;
            }

            $itemPlan = $this->planRegistry->getPlan($item::class);

            if (null === $itemPlan) {
                continue;
            }

            yield ItemOfIterableMatchingRule::forPlan($key, $propertyRuleSet, $itemPlan, $item);
        }
    }
}
