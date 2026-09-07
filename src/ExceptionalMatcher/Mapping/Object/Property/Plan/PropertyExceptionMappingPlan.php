<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan;

use ArrayIterator;
use PhPhD\ExceptionalMatcher\Mapping\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\CatchAttributesExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Matcher\ExceptionMatcherAggregateAdapter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable\IterablePropertyExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\PropertyExceptionMappingNode;
use ReflectionProperty;
use Throwable;

use function is_iterable;
use function is_object;

/** @internal */
final class PropertyExceptionMappingPlan
{
    public function __construct(
        private readonly ReflectionProperty $property,
        /** @var iterable<CatchExceptionMappingPlan<Throwable>> */
        private readonly iterable $catchPlans,
        /** @var ObjectExceptionMappingPlanRegistry<object> */
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
    ) {
    }

    public function bind(ObjectExceptionMappingNode $objectNode): PropertyExceptionMappingNode
    {
        $name = $this->getName();
        $value = $this->getPropertyValue($objectNode->getEnclosingObject());

        /** @var ArrayIterator<int,ExceptionMatcher> $matchers */
        $matchers = new ArrayIterator();

        $propertyNode = new PropertyExceptionMappingNode($objectNode, $name, $value, $matchers);

        if (null !== $catchMatcher = $this->catchAttributesMatcher($propertyNode)) {
            $matchers->append(new ExceptionMatcherAggregateAdapter($catchMatcher));
        }

        if (null !== $nestedObjectMatcher = $this->nestedObjectMatcher($propertyNode)) {
            $matchers->append($nestedObjectMatcher);
        } elseif (null !== $nestedIterableMatcher = $this->nestedObjectsOfIterableMatcher($propertyNode)) {
            $matchers->append(new ExceptionMatcherAggregateAdapter($nestedIterableMatcher));
        }

        return $propertyNode;
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
     * @return iterable<CatchExceptionMappingPlan<Throwable>>
     */
    public function getCatchPlans(): iterable
    {
        return $this->catchPlans;
    }

    /** @noinspection PhpLoopNeverIteratesInspection */
    public function hasCatchPlans(): bool
    {
        /** @psalm-suppress UnusedForeachValue */
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

    private function catchAttributesMatcher(PropertyExceptionMappingNode $property): ?ExceptionMatcherAggregate
    {
        if (!$this->hasCatchPlans()) {
            return null;
        }

        return new CatchAttributesExceptionMatcherAggregate($property, $this->catchPlans);
    }

    private function nestedObjectMatcher(PropertyExceptionMappingNode $property): ?ExceptionMatcher
    {
        $value = $property->getValue();

        if (!is_object($value)) {
            return null;
        }

        $nestedPlan = $this->planRegistry->getPlan($value::class);

        /** @noinspection PhpNullSafeOperatorCanBeUsedInspection */
        if (null === $nestedPlan) {
            return null;
        }

        return $nestedPlan->bind($value, $property);
    }

    private function nestedObjectsOfIterableMatcher(PropertyExceptionMappingNode $property): ?ExceptionMatcherAggregate
    {
        $value = $property->getValue();

        if (!is_iterable($value) || [] === $value) {
            return null;
        }

        return new IterablePropertyExceptionMatcherAggregate($property, $this->planRegistry);
    }
}
