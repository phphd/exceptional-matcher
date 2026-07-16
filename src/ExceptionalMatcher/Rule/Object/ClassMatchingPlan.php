<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use Generator;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyPlan;
use Webmozart\Assert\Assert;

/** @internal */
final class ClassMatchingPlan
{
    public function __construct(
        /** @var iterable<int,PropertyPlan> */
        private readonly iterable $propertyPlans,
    ) {
    }

    /** @return iterable<int,PropertyPlan> */
    public function getPropertyPlans(): iterable
    {
        return $this->propertyPlans;
    }

    /** @psalm-suppress UnusedVariable the by-reference closure capture is the usage */
    public function bind(object $object, ?MatchingRule $ownerRule = null): MatchingRule
    {
        /** @var ?ObjectMatchingRuleSet $objectRuleSet */
        $objectRuleSet = null;

        // the rules generator runs only once the rule set processes it,
        // by which point the by-reference variable is already assigned
        $propertyRules = (function () use (&$objectRuleSet): Generator {
            Assert::isInstanceOf($objectRuleSet, ObjectMatchingRuleSet::class);

            foreach ($this->propertyPlans as $propertyPlan) {
                yield $propertyPlan->bind($objectRuleSet);
            }
        })();

        return $objectRuleSet = new ObjectMatchingRuleSet($object, $ownerRule, $propertyRules);
    }
}
