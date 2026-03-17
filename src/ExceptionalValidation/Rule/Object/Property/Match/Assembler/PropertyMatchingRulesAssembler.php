<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler;

use ArrayIterator;
use Generator;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\MatchExceptionRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyMatchingRuleSet;
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

    /** @param MatchConditionFactory<Throwable> $conditionFactory */
    public function assembleRules(MatchConditionFactory $conditionFactory): ?CompositeMatchingRule
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeMatchingRule($this->parentRule, $rules);

        foreach ($this->getCatchAttributes() as $catchAttribute) {
            $condition = $conditionFactory->getCondition($catchAttribute, $this->parentRule);

            Assert::notNull($condition);

            $rules->append(new MatchExceptionRule(
                $ruleSet,
                $condition,
                $catchAttribute->getMessage(),
                $catchAttribute->getFormatter(),
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
