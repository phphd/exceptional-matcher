<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Condition;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;

/** @internal */
interface MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): ?MatchCondition;
}
