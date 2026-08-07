<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan;

use AppendIterator;
use InvalidArgumentException;
use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\ObjectExceptionMappingNode;

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
    public function bind(object $object, ?ExceptionMappingNode $ownerRule = null): ObjectExceptionMappingNode
    {
        if (!$object instanceof $this->className) {
            throw new InvalidArgumentException(sprintf('Expected object of type "%s", got "%s".', $this->className, $object::class));
        }

        $objectRuleSet = new ObjectExceptionMappingNode($object, $ownerRule, new ReusableIteratorAggregate($propertyRules = new AppendIterator()));

        $propertyRules->append($this->bindPropertyRules($objectRuleSet));

        return $objectRuleSet;
    }

    /** @return iterable<PropertyExceptionMappingPlan> */
    public function getPropertyPlans(): iterable
    {
        return $this->propertyPlans;
    }

    /** @noinspection PhpLoopNeverIteratesInspection */
    public function hasPropertyPlans(): bool
    {
        foreach ($this->propertyPlans as $catchPlan) {
            return true;
        }

        return false;
    }

    /** @return Iterator<ExceptionMappingNode> */
    private function bindPropertyRules(ObjectExceptionMappingNode $objectRuleSet): Iterator
    {
        foreach ($this->propertyPlans as $propertyPlan) {
            yield $propertyPlan->bind($objectRuleSet);
        }
    }
}
