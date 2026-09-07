<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan;

use AppendIterator;
use InvalidArgumentException;
use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;

use function sprintf;

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
        if (!$object instanceof $this->className) {
            throw new InvalidArgumentException(sprintf('Expected object of type "%s", got "%s".', $this->className, $object::class));
        }

        /** @var AppendIterator<int,ExceptionMappingNode,Iterator<ExceptionMappingNode>> $properties */
        $properties = new AppendIterator();

        $objectNode = new ObjectExceptionMappingNode($object, $parentProperty, new ReusableIteratorAggregate($properties));

        $properties->append($this->bindPropertyPlans($objectNode));

        return $objectNode;
    }

    /** @return iterable<PropertyExceptionMappingPlan> */
    public function getPropertyPlans(): iterable
    {
        return $this->propertyPlans;
    }

    /** @noinspection PhpLoopNeverIteratesInspection */
    public function hasPropertyPlans(): bool
    {
        /** @psalm-suppress UnusedForeachValue */
        foreach ($this->propertyPlans as $catchPlan) {
            return true;
        }

        return false;
    }

    /** @return Iterator<ExceptionMappingNode> */
    private function bindPropertyPlans(ObjectExceptionMappingNode $objectNode): Iterator
    {
        foreach ($this->propertyPlans as $propertyPlan) {
            yield $propertyPlan->bind($objectNode);
        }
    }
}
