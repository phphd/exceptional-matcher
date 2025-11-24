<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<PropertyRuleSetAssemblerEnvelope>
 */
final readonly class PropertyRuleSetAssembler implements CaptureRuleSetAssembler
{
    /** @param CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope> $captureListAssembler */
    public function __construct(
        private CaptureRuleSetAssembler $captureListAssembler,
    ) {
    }

    /** @param PropertyRuleSetAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?PropertyRuleSet
    {
        return $envelope->assemble($parentRule, $this->captureListAssembler);
    }
}
