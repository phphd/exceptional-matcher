<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler;

use ArrayIterator;
use Generator;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\MatchExceptionRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMatchingRuleSet;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class PropertyMatchingRulesAssembler implements MatchingRuleSetAssembler
{
    public function __construct(
        private readonly PropertyMatchingRuleSet $parentRule,
        private readonly ReflectionProperty $reflectionProperty,
    ) {
    }

    /** @param MatchConditionFactory<Throwable> $matchConditionFactory */
    public function assembleRules(MatchConditionFactory $matchConditionFactory): ?CompositeMatchingRule
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeMatchingRule($this->parentRule, $rules);

        foreach ($this->getCatchAttributes() as $catchAttribute) {
            $condition = $matchConditionFactory->getCondition($catchAttribute, $this->parentRule);

            Assert::notNull($condition);

            $rules->append(new MatchExceptionRule(
                $ruleSet,
                $condition,
                $catchAttribute->getFormat(),
                $catchAttribute->getMessage(),
            ));
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }

    public function getParentRule(): PropertyMatchingRuleSet
    {
        return $this->parentRule;
    }

    /** @return Generator<Catch_<Throwable,Throwable>> */
    private function getCatchAttributes(): Generator
    {
        $catchAttributes = $this->reflectionProperty->getAttributes(Catch_::class);

        foreach ($catchAttributes as $catchAttribute) {
            yield $catchAttribute->newInstance();
        }
    }
}
