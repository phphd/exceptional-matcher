<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property;

use AppendIterator;
use Generator;
use Iterator;
use PhPhD\ExceptionalMatcher\Rule\ItemOfIterableMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMatchingRuleSet;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
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
        /** @var iterable<CatchPlan<Throwable>> */
        private readonly iterable $catchPlans,
        private readonly ClassMatchingPlanRegistry $planRegistry,
    ) {
    }

    /** @psalm-suppress UnusedVariable the by-reference closure capture is the usage */
    public function bind(ObjectMatchingRuleSet $objectRuleSet): MatchingRule
    {
        $name = $this->getName();
        $value = $this->getPropertyValue($objectRuleSet->getValue());

        $propertyRuleSet = new PropertyMatchingRuleSet($objectRuleSet, $name, $value, new ReusableIteratorAggregate($rules = new AppendIterator()));

        $rules->append($this->catchRules($propertyRuleSet));
        $rules->append($this->getNestedObjectRules($value, $propertyRuleSet));
        $rules->append($this->getIterableItemRules($value, $propertyRuleSet));

        return $propertyRuleSet;
    }

    private function getPropertyValue(object $object): mixed
    {
        if (!$this->property->isInitialized($object)) {
            return null;
        }

        return $this->property->getValue($object);
    }

    /**
     * @param PropertyMatchingRuleSet $propertyRuleSet
     *
     * @return Iterator<MatchingRule>
     */
    private function catchRules(PropertyMatchingRuleSet $propertyRuleSet): Iterator
    {
        foreach ($this->catchPlans as $catchPlan) {
            yield $catchPlan->bind($propertyRuleSet);
        }
    }

    /** @return Iterator<MatchingRule> */
    private function getNestedObjectRules(mixed $value, PropertyMatchingRuleSet $propertyRuleSet): Iterator
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

    /** @return Iterator<MatchingRule> */
    private function getIterableItemRules(mixed $value, PropertyMatchingRuleSet $propertyRuleSet): Iterator
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

            yield new ItemOfIterableMatchingRule(
                $key,
                $propertyRuleSet,
                fn (ItemOfIterableMatchingRule $self) => $itemPlan->bind($item, $self),
            );
        }
    }

    public function getName(): string
    {
        return $this->property->getName();
    }

    /**
     * @api the seam for the mapping linter: forcing this iterable compiles every `#[Catch_]` of the property
     *
     * @return iterable<CatchPlan<Throwable>>
     */
    public function getCatchPlans(): iterable
    {
        return $this->catchPlans;
    }

}
