<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler;

use PhPhD\ExceptionalValidation\Model\CaptureRule;

/**
 * @internal
 *
 * @template TEnvelope of object
 */
interface CaptureRuleSetAssembler
{
    /** @param TEnvelope&CaptureRuleSetAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule;
}
