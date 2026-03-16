<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Rule\MatchingRule;

/** @internal */
interface MatchingRuleSetAssembler
{
    public function getParentRule(): ?MatchingRule;
}
