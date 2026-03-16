<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class;

use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class ExceptionClassMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, CaptureRule $parent): ExceptionClassMatchCondition
    {
        return new ExceptionClassMatchCondition($catch->getExceptionClass());
    }
}
