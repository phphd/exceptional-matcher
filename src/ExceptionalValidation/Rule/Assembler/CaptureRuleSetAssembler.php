<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Rule\CaptureRule;

/** @internal */
interface CaptureRuleSetAssembler
{
    public function getParentRule(): ?CaptureRule;
}
