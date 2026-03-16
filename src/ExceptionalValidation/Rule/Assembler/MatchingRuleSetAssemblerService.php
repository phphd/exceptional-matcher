<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Rule\MatchingRule;

/**
 * @internal
 *
 * @template TAssembler of MatchingRuleSetAssembler
 */
interface MatchingRuleSetAssemblerService
{
    /** @param TAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?MatchingRule;
}
