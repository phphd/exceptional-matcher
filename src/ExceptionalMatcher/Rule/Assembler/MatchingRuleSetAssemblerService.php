<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Assembler;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;

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
