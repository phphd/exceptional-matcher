<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler;

use ArrayIterator;
use Generator;
use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class PropertyCaptureRulesAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private readonly PropertyRuleSet $parentRule,
        private readonly ReflectionProperty $reflectionProperty,
    ) {
    }

    /** @param MatchConditionFactory<Throwable> $conditionFactory */
    public function assembleCaptureRules(MatchConditionFactory $conditionFactory): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeRuleSet($this->parentRule, $rules);

        foreach ($this->getCatchAttributes() as $catch) {
            $condition = $conditionFactory->getCondition($catch, $this->parentRule);

            Assert::notNull($condition);

            $rules->append(new CaptureExceptionRule(
                $ruleSet,
                $condition,
                $catch->getMessage(),
                $catch->getFormatter(),
            ));
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }

    public function getParentRule(): PropertyRuleSet
    {
        return $this->parentRule;
    }

    /** @return Generator<Catch_<Throwable,Throwable>> */
    private function getCatchAttributes(): Generator
    {
        $catchAttributes = $this->reflectionProperty->getAttributes(Catch_::class);

        foreach ($catchAttributes as $captureAttribute) {
            yield $captureAttribute->newInstance();
        }
    }
}
