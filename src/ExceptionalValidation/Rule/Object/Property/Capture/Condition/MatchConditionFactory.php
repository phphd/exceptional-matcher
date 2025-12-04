<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use Throwable;

/** @template T of Throwable */
interface MatchConditionFactory
{
    /**
     * @param Capture<Throwable,Throwable> $capture
     *
     * @return ?MatchCondition<T>
     */
    public function getCondition(Capture $capture, CaptureRule $parent): ?MatchCondition;
}
