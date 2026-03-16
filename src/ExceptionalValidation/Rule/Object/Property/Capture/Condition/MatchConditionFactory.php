<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use Throwable;

/**
 * @internal (this might be exposed as an api in some future versions)
 *
 * @template T of Throwable
 */
interface MatchConditionFactory
{
    /**
     * @param Catch_<T,T> $catch
     *
     * @return ?MatchCondition<T>
     */
    public function getCondition(Catch_ $catch, CaptureRule $parent): ?MatchCondition;
}
