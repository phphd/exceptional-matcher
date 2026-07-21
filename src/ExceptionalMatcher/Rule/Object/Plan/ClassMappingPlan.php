<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Plan;

use AppendIterator;
use Iterator;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMappingRuleSet;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingPlan;

/** @internal */
final class ClassMappingPlan
{
    public function __construct(
        /** @var iterable<PropertyMappingPlan> */
        private readonly iterable $propertyPlans,
    ) {
    }

    public function bind(object $object, ?MappingRule $ownerRule = null): ObjectMappingRuleSet
    {
        $objectRuleSet = new ObjectMappingRuleSet($object, $ownerRule, new ReusableIteratorAggregate($propertyRules = new AppendIterator()));

        $propertyRules->append($this->bindPropertyRules($objectRuleSet));

        return $objectRuleSet;
    }

    /** @return Iterator<MappingRule> */
    private function bindPropertyRules(ObjectMappingRuleSet $objectRuleSet): Iterator
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
}
