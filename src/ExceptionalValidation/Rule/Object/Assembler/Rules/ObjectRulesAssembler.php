<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerEnvelope;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<ObjectRulesAssemblerEnvelope>
 */
final readonly class ObjectRulesAssembler implements CaptureRuleSetAssembler
{
    /** @param CaptureRuleSetAssembler<PropertyRuleSetAssemblerEnvelope> $propertyRuleSetAssembler */
    public function __construct(
        private CaptureRuleSetAssembler $propertyRuleSetAssembler,
    ) {
    }

    /** @param ObjectRulesAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        return $envelope->assemble($parentRule, $this->propertyRuleSetAssembler);
    }
}
