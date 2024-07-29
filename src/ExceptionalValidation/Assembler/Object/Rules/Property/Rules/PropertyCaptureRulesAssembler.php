<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Model\Condition\CompositeMatchCondition;
use PhPhD\ExceptionalValidation\Model\Condition\MatchByExceptionClassCondition;
use PhPhD\ExceptionalValidation\Model\Condition\MatchWithClosureCondition;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureExceptionRule;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\CompositeRuleSet;

use function array_filter;
use function array_values;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>
 */
final class PropertyCaptureRulesAssembler implements CaptureRuleSetAssembler
{
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

            $condition = $this->getCondition($capture, $parent);

            $rules->append(new CaptureExceptionRule(
                $ruleSet,
                $condition->compile(),
                $capture->getMessage(),
                $capture->getFormatter(),
            ));
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }

    private function getCondition(Capture $capture, CaptureRule $parent): CompositeMatchCondition
    {
        $conditions = [];

        $conditions[] = $this->getExceptionClassCondition($capture);
        $conditions[] = $this->getClosureCondition($capture, $parent);

        return new CompositeMatchCondition(array_values(array_filter($conditions)));
    }

    private function getExceptionClassCondition(Capture $capture): MatchByExceptionClassCondition
    {
        return new MatchByExceptionClassCondition($capture->getExceptionClass());
    }

    private function getClosureCondition(Capture $capture, CaptureRule $parent): ?MatchWithClosureCondition
    {
        $when = $capture->getWhen();

        if (null === $when) {
            return null;
        }

        $object = $parent->getEnclosingObject();

        if ($when[0] === $object::class) {
            $when = [$object, $when[1]];
        }

        /** @phpstan-ignore-next-line */
        return new MatchWithClosureCondition($when(...));
    }
}
