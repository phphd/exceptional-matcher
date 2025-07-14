<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Rule\CaptureRule;

/**
 * @internal
 *
 * @template TEnvelope of object
 */
interface CaptureRuleSetAssembler
{
    /** @param TEnvelope&CaptureRuleSetAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule;
}
