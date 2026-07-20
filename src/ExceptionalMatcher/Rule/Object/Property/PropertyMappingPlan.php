<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property;

use AppendIterator;
use ArrayIterator;
use Iterator;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMatchingRuleSet;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher\CatchAttributesExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher\IterablePropertyExceptionMatcher;
use ReflectionProperty;
use Throwable;

use function is_object;

/** @internal */
final class PropertyMappingPlan
{
    public function __construct(
        private readonly ReflectionProperty $property,
        /** @var iterable<CatchPlan<Throwable>> */
        private readonly iterable $catchPlans,
        private readonly ClassMatchingPlanRegistry $planRegistry,
    ) {
    }

    public function bind(ObjectMatchingRuleSet $ownerRule): PropertyMatchingRuleSet
    {
        $name = $this->getName();
        $value = $this->getPropertyValue($ownerRule->getEnclosingObject());

        $propertyRuleSet = new PropertyMatchingRuleSet($ownerRule, $name, $value, new ReusableIteratorAggregate($rules = new AppendIterator()));

        if (null !== $catchRules = $this->bindCatchRules($propertyRuleSet)) {
            $rules->append($catchRules->getExceptionMatchers());
        }
        $rules->append($this->bindNestedObjectPlan($propertyRuleSet));
        $rules->append($this->bindIterableItemsPlans($propertyRuleSet));

        return $propertyRuleSet;
    }

    private function getPropertyValue(object $object): mixed
    {
        if (!$this->property->isInitialized($object)) {
            return null;
        }

        return $this->property->getValue($object);
    }

    /** @param PropertyMatchingRuleSet $propertyRuleSet */
    private function bindCatchRules(PropertyMatchingRuleSet $propertyRuleSet): ?ExceptionMatcherAggregate
    {
        /** @noinspection PhpLoopNeverIteratesInspection */
        foreach ($this->catchPlans as $catchPlan) {
            return new CatchAttributesExceptionMatcherAggregate($propertyRuleSet, $this->catchPlans);
        }

        return null;
    }

    /** @return Iterator<MatchingRule> */
    private function bindNestedObjectPlan(PropertyMatchingRuleSet $propertyRuleSet): Iterator
    {
        $value = $propertyRuleSet->getValue();

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
    private function bindIterableItemsPlans(PropertyMatchingRuleSet $propertyRuleSet): Iterator
    {
        $value = $propertyRuleSet->getValue();

        if (!is_iterable($value) || [] === $value) {
            return;
        }

        yield from (new IterablePropertyExceptionMatcher($propertyRuleSet, $this->planRegistry))->getExceptionMatchers();
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
