<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property;

use AppendIterator;
use ArrayIterator;
use Exception;
use Iterator;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregate;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRuleAggregateAdapter;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Compiler\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Rule\Object\Compiler\CatchPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMappingRuleSet;
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

    public function bind(ObjectMappingRuleSet $ownerRule): PropertyMappingRuleSet
    {
        $name = $this->getName();
        $value = $this->getPropertyValue($ownerRule->getEnclosingObject());

        $propertyRuleSet = new PropertyMappingRuleSet($ownerRule, $name, $value, ($rules = new ArrayIterator()));

        if (null !== $catchRules = $this->catchAttributesMatcher($propertyRuleSet)) {
            $rules->append(new ExceptionMatchingRuleAggregateAdapter($catchRules));
        }
        if (null !== $nestedObjectRule = $this->nestedObjectMatcher($propertyRuleSet)) {
            $rules->append($nestedObjectRule);
        }
        if (null !== $nestedIterableRule = $this->nestedObjectsOfIterableMatcher($propertyRuleSet)) {
            $rules->append(new ExceptionMatchingRuleAggregateAdapter($nestedIterableRule));
        }

        return $propertyRuleSet;
    }

    private function getPropertyValue(object $object): mixed
    {
        if (!$this->property->isInitialized($object)) {
            return null;
        }

        return $this->property->getValue($object);
    }

    private function catchAttributesMatcher(PropertyMappingRuleSet $propertyRuleSet): ?ExceptionMatchingRuleAggregate
    {
        if (!$this->hasCatchPlans()) {
            return null;
        }

        return new CatchAttributesExceptionMatcherAggregate($propertyRuleSet, $this->catchPlans);
    }

    private function nestedObjectMatcher(PropertyMappingRuleSet $propertyRuleSet): ?ExceptionMatchingRule
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

    private function nestedObjectsOfIterableMatcher(PropertyMappingRuleSet $propertyRuleSet): ?ExceptionMatchingRuleAggregate
    {
        $value = $propertyRuleSet->getValue();

        if (!is_iterable($value) || [] === $value) {
            return null;
        }

        return new IterablePropertyExceptionMatcher($propertyRuleSet, $this->planRegistry);
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
     * @return iterable<CatchPlan<Throwable>>
     */
    public function getCatchPlans(): iterable
    {
        return $this->catchPlans;
    }

    /** @noinspection PhpLoopNeverIteratesInspection */
    public function hasCatchPlans(): bool
    {
        try {
            foreach ($this->catchPlans as $catchPlan) {
                return true;
            }
        } catch (CatchAttributeInstantiationFailedException|CatchPlanCompilationFailedException) {
            // Since plans are instantiated lazily, we don't want to propagate those exceptions right now.
            // They will eventually propagate on the first traversal attempt due to ReusableIteratorAggregate implementation.
            return true;
        }

        return false;
    }
}
