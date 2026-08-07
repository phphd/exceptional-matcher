<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\entos\Plan;

use ArrayIterator;
use PhPhD\ExceptionalMatcher\Mapping\Object\entos\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\entos\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\PropertyExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregate;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregateAdapter;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher\CatchAttributesExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Matcher\IterablePropertyExceptionMatcher;
use ReflectionProperty;

use function is_iterable;
use function is_object;

/** @internal */
final class PropertyExceptionMappingPlan
{
    public function __construct(
        private readonly ReflectionProperty $property,
        /** @var iterable<CatchExceptionMappingPlan> */
        private readonly iterable $catchPlans,
        /** @var ObjectExceptionMappingPlanRegistry<object> */
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
    ) {
    }

    public function bind(ObjectExceptionMappingNode $ownerRule): PropertyExceptionMappingNode
    {
        $name = $this->getName();
        $value = $this->getPropertyValue($ownerRule->getEnclosingObject());

        $propertyRuleSet = new PropertyExceptionMappingNode($ownerRule, $name, $value, ($rules = new ArrayIterator()));

        if (null !== $catchRules = $this->catchAttributesMatcher($propertyRuleSet)) {
            $rules->append(new ExceptionMatchingRuleAggregateAdapter($catchRules));
        }

        if (null !== $nestedObjectRule = $this->nestedObjectMatcher($propertyRuleSet)) {
            $rules->append($nestedObjectRule);
        } elseif (null !== $nestedIterableRule = $this->nestedObjectsOfIterableMatcher($propertyRuleSet)) {
            $rules->append(new ExceptionMatchingRuleAggregateAdapter($nestedIterableRule));
        }

        return $propertyRuleSet;
    }

    public function getName(): string
    {
        return $this->property->getName();
    }

    public function getProperty(): ReflectionProperty
    {
        return $this->property;
    }

    /**
     * @api the seam for the mapping linter: forcing this iterable compiles every `#[Catch_]` of the property
     *
     * @return iterable<CatchExceptionMappingPlan>
     */
    public function getCatchPlans(): iterable
    {
        return $this->catchPlans;
    }

    /** @noinspection PhpLoopNeverIteratesInspection */
    public function hasCatchPlans(): bool
    {
        foreach ($this->catchPlans as $catchPlan) {
            return true;
        }

        return false;
    }

    private function getPropertyValue(object $object): mixed
    {
        if (!$this->property->isInitialized($object)) {
            return null;
        }

        return $this->property->getValue($object);
    }

    private function catchAttributesMatcher(PropertyExceptionMappingNode $propertyRuleSet): ?ExceptionMatchingRuleAggregate
    {
        if (!$this->hasCatchPlans()) {
            return null;
        }

        return new CatchAttributesExceptionMatcherAggregate($propertyRuleSet, $this->catchPlans);
    }

    private function nestedObjectMatcher(PropertyExceptionMappingNode $propertyRuleSet): ?ExceptionMatchingRule
    {
        $value = $propertyRuleSet->getValue();

        if (!is_object($value)) {
            return null;
        }

        $nestedPlan = $this->planRegistry->getPlan($value::class);

        /** @noinspection PhpNullSafeOperatorCanBeUsedInspection */
        if (null === $nestedPlan) {
            return null;
        }

        return $nestedPlan->bind($value, $propertyRuleSet);
    }

    private function nestedObjectsOfIterableMatcher(PropertyExceptionMappingNode $propertyRuleSet): ?ExceptionMatchingRuleAggregate
    {
        $value = $propertyRuleSet->getValue();

        if (!is_iterable($value) || [] === $value) {
            return null;
        }

        return new IterablePropertyExceptionMatcher($propertyRuleSet, $this->planRegistry);
    }
}
