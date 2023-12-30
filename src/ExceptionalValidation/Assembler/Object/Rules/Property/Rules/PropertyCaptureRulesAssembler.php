<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Model\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rules\CaptureExceptionRule;
use PhPhD\ExceptionalValidation\Model\Sets\CompositeRuleSet;

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
            $capture = $captureAttribute->newInstance();

            $rules->append(new CaptureExceptionRule($ruleSet, $capture->getExceptionClass(), $capture->getMessage()));
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }
}
