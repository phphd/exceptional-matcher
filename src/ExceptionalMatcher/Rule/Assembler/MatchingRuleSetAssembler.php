<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Assembler;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;

/** @internal */
interface MatchingRuleSetAssembler
{
    public function getParentRule(): ?MatchingRule;
}
