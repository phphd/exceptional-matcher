<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use ReflectionProperty;
use Throwable;

/** @internal */
final readonly class PropertyRulesAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private ReflectionProperty $reflectionProperty,
    ) {
    }

    public function assembleCaptureRules(CaptureRule $parentRule, MatchConditionFactory $conditionFactory): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeRuleSet($parentRule, $rules);

        $captureAttributes = $this->reflectionProperty->getAttributes(Capture::class);

        foreach ($captureAttributes as $captureAttribute) {
            $capture = $captureAttribute->newInstance();

            /** @var MatchCondition<Throwable> $condition */
            $condition = $conditionFactory->getCondition($capture, $parentRule);

            $rules->append(new CaptureExceptionRule(
                $ruleSet,
                $condition,
                $capture->getMessage(),
                $capture->getFormatter(),
            ));
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }

    public function getReflectionProperty(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }
}
