<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Plan;

use AppendIterator;
use Iterator;
use PhPhD\ExceptionalMatcher\Rule\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingPlan;
use ReflectionClass;

/**
 * @internal
 *
 * @template T of object
 */
final class ClassMappingPlan
{
    public function __construct(
        /** @var class-string<T> */
        private readonly string $className,
        /** @var iterable<PropertyMappingPlan> */
        private readonly iterable $propertyPlans,
    ) {
    }

    /** @param T $object */
    public function bind(object $object, ?ExceptionMappingNode $ownerRule = null): ObjectExceptionMappingNode
    {
        if (!$object instanceof $this->className) {
            throw new \InvalidArgumentException(sprintf('Expected object of type "%s", got "%s".', $this->className, $object::class));
        }

        $objectRuleSet = new ObjectExceptionMappingNode($object, $ownerRule, new ReusableIteratorAggregate($propertyRules = new AppendIterator()));

        $propertyRules->append($this->bindPropertyRules($objectRuleSet));

        return $objectRuleSet;
    }

    /** @return Iterator<ExceptionMappingNode> */
    private function bindPropertyRules(ObjectExceptionMappingNode $objectRuleSet): Iterator
    {
        foreach ($this->propertyPlans as $propertyPlan) {
            yield $propertyPlan->bind($objectRuleSet);
        }
    }

    /** @return iterable<PropertyMappingPlan> */
    public function getPropertyPlans(): iterable
    {
        return $this->propertyPlans;
    }

    /** @noinspection PhpLoopNeverIteratesInspection */
    public function hasPropertyPlans(): bool
    {
        foreach ($this->propertyPlans as $propertyPlan) {
            return true;
        }

        return false;
    }
}
