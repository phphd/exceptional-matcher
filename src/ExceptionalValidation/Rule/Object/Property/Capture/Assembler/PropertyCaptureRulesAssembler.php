<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler;

use ArrayIterator;
use Generator;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final readonly class PropertyCaptureRulesAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private PropertyRuleSet $parentRule,
        private ReflectionProperty $reflectionProperty,
    ) {
    }

    /** @param MatchConditionFactory<Throwable> $conditionFactory */
    public function assembleCaptureRules(MatchConditionFactory $conditionFactory): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeRuleSet($this->parentRule, $rules);

        foreach ($this->getCaptures() as $capture) {
            $condition = $conditionFactory->getCondition($capture, $this->parentRule);

            Assert::notNull($condition);

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

    public function getParentRule(): PropertyRuleSet
    {
        return $this->parentRule;
    }

    /** @return Generator<Capture<Throwable,Throwable>> */
    private function getCaptures(): Generator
    {
        $captureAttributes = $this->reflectionProperty->getAttributes(Capture::class);

        foreach ($captureAttributes as $captureAttribute) {
            yield $captureAttribute->newInstance();
        }
    }
}
