<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use Throwable;

/** @internal */
interface MatchConditionFactory
{
    /**
     * @phpstan-return ?MatchCondition<covariant Throwable>
     *
     * @psalm-return ?MatchCondition<Throwable>
     */
    public function getCondition(Capture $capture, CaptureRule $parent): ?MatchCondition;
}
