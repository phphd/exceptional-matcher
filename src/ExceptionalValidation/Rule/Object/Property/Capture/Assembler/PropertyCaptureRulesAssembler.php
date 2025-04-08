<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>
 */
final class PropertyCaptureRulesAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private readonly MatchConditionFactory $conditionFactory,
    ) {
    }

    /** @param PropertyRulesAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeRuleSet($parent, $rules);

        $captureAttributes = $envelope
            ->getReflectionProperty()
            ->getAttributes(Capture::class)
        ;

        foreach ($captureAttributes as $captureAttribute) {
            /**
             * @psalm-suppress UnnecessaryVarAnnotation
             *
             * @var Capture $capture
             */
            $capture = $captureAttribute->newInstance();

            $rules->append(new CaptureExceptionRule(
                $ruleSet,
                $this->getCondition($capture, $parent),
                $capture->getMessage(),
                $capture->getFormatter(),
            ));
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }

    /** @return MatchCondition<Throwable> */
    private function getCondition(Capture $capture, CaptureRule $parent): MatchCondition
    {
        /** @var MatchCondition<Throwable> */
        return $this->conditionFactory->getCondition($capture, $parent);
    }
}
