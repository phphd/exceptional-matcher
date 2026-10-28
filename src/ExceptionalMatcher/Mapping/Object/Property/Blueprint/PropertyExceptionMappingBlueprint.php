<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint;

use ArrayIterator;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Matcher\ExceptionMatcherAggregateAdapter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\CatchAttributesExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable\IterablePropertyExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\PropertyExceptionMappingNode;
use ReflectionProperty;
use Throwable;

use function is_iterable;
use function is_object;

/**
 * @internal
 *
 * @implements ExceptionMappingBlueprint<ObjectExceptionMappingNode,PropertyExceptionMappingNode>
 */
final class PropertyExceptionMappingBlueprint implements ExceptionMappingBlueprint
{
    public function __construct(
        private readonly ReflectionProperty $property,
        /** @var iterable<CatchExceptionMappingBlueprint<Throwable>> */
        private readonly iterable $catchPrints,
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $planRegistry,
    ) {
    }

    /** @param ObjectExceptionMappingNode $embracingNode */
    public function bind(ExceptionMappingNode $embracingNode): PropertyExceptionMappingNode
    {
        $name = $this->getName();
        $value = $this->getPropertyValue($embracingNode->getObject());

        /** @var ArrayIterator<int,ExceptionMatcher> $matchers */
        $matchers = new ArrayIterator();

        $propertyNode = new PropertyExceptionMappingNode($embracingNode, $name, $value, $matchers);

        if (null !== $catchMatcher = $this->catchAttributesMatcher($propertyNode)) {
            $matchers->append(new ExceptionMatcherAggregateAdapter($catchMatcher));
        }

        if (null !== $innerObjectMatcher = $this->innerObjectMatcher($propertyNode)) {
            $matchers->append($innerObjectMatcher);
        } elseif (null !== $innerObjectsIterableMatcher = $this->innerObjectsIterableMatcher($propertyNode)) {
            $matchers->append(new ExceptionMatcherAggregateAdapter($innerObjectsIterableMatcher));
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
     * @return iterable<CatchExceptionMappingBlueprint<Throwable>>
     */
    public function getCatchPrints(): iterable
    {
        return $this->catchPrints;
    }

    /** @psalm-suppress UnusedForeachValue */
    public function hasCatchPrints(): bool
    {
        /** @noinspection PhpLoopNeverIteratesInspection */
        foreach ($this->catchPrints as $catchPlan) {
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
        if (!$this->hasCatchPrints()) {
            return null;
        }

        return new CatchAttributesExceptionMatcherAggregate($property, $this->catchPrints);
    }

    private function innerObjectMatcher(PropertyExceptionMappingNode $property): ?ExceptionMatcher
    {
        if (!is_object($object = $property->getValue())) {
            return null;
        }

        $innerPlan = $this->planRegistry->getMappingBlueprint($object::class);

        /** @noinspection PhpNullSafeOperatorCanBeUsedInspection */
        if (null === $innerPlan) {
            return null;
        }

        return $innerPlan->bind($property, $object);
    }

    private function innerObjectsIterableMatcher(PropertyExceptionMappingNode $property): ?ExceptionMatcherAggregate
    {
        $value = $property->getValue();

        if (!is_iterable($value) || [] === $value) {
            return null;
        }

        return new IterablePropertyExceptionMatcherAggregate($property, $this->planRegistry);
    }
}
