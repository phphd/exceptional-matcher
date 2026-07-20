<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Plan;

use AppendIterator;
use Iterator;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMatchingRuleSet;
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

    public function bind(object $object, ?MatchingRule $ownerRule = null): ObjectMatchingRuleSet
    {
        $objectRuleSet = new ObjectMatchingRuleSet($object, $ownerRule, new ReusableIteratorAggregate($propertyRules = new AppendIterator()));

        $propertyRules->append($this->bindPropertyRules($objectRuleSet));

        return $objectRuleSet;
    }

    /** @return Iterator<MatchingRule> */
    private function bindPropertyRules(ObjectMatchingRuleSet $objectRuleSet): Iterator
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
