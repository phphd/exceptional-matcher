<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint;

use AppendIterator;
use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @template T of object
 */
final class ObjectExceptionMappingPlan
{
    public function __construct(
        /** @var class-string<T> */
        private readonly string $className,
        /** @var iterable<PropertyExceptionMappingPlan> */
        private readonly iterable $propertyPlans,
    ) {
    }

    /** @param T $object */
    public function bind(object $object, ?ExceptionMappingNode $parentProperty = null): ObjectExceptionMappingNode
    {
        Assert::isInstanceOf($object, $this->className);

        /** @var AppendIterator<int,ExceptionMappingNode,Iterator<int,ExceptionMappingNode>> $properties */
        $properties = new AppendIterator();

        $objectNode = new ObjectExceptionMappingNode($object, $parentProperty, new MemoizingIteratorAggregate($properties));

        $properties->append($this->bindPropertyPlans($objectNode));

        return $objectNode;
    }

    /** @return iterable<PropertyExceptionMappingPlan> */
    public function getPropertyPlans(): iterable
    {
        return $this->propertyPlans;
    }

    /** @psalm-suppress UnusedForeachValue */
    public function hasPropertyPlans(): bool
    {
        /** @noinspection PhpLoopNeverIteratesInspection */
        foreach ($this->propertyPlans as $propertyPlan) {
            return true;
        }

        return false;
    }

    /** @return Iterator<int,ExceptionMappingNode> */
    private function bindPropertyPlans(ObjectExceptionMappingNode $objectNode): Iterator
    {
        foreach ($this->propertyPlans as $propertyPlan) {
            yield $propertyPlan->bind($objectNode);
        }
    }
}
