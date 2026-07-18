<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use AppendIterator;
use Iterator;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyPlan;

/** @internal */
final class ClassMatchingPlan
{
    public function __construct(
        /** @var iterable<PropertyPlan> */
        private readonly iterable $propertyPlans,
    ) {
    }

    /** @psalm-suppress UnusedVariable the by-reference closure capture is the usage */
    public function bind(object $object, ?MatchingRule $ownerRule = null): MatchingRule
    {
        $objectRuleSet = new ObjectMatchingRuleSet($object, $ownerRule, new ReusableIteratorAggregate($propertyRules = new AppendIterator()));
        $propertyRules->append($this->propertyRules($objectRuleSet));

        return $objectRuleSet;
    }

    /** @return Iterator<MatchingRule> */
    private function propertyRules(ObjectMatchingRuleSet $objectRuleSet): Iterator
    {
        foreach ($this->propertyPlans as $propertyPlan) {
            yield $propertyPlan->bind($objectRuleSet);
        }
    }

    /** @return iterable<PropertyPlan> */
    public function getPropertyPlans(): iterable
    {
        return $this->propertyPlans;
    }
}
