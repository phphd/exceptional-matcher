<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Rule\CaptureRule;

/**
 * @internal
 *
 * @template TAssembler of CaptureRuleSetAssembler
 */
interface CaptureRuleSetAssemblerService
{
    /** @param TAssembler $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CaptureRule;
}
