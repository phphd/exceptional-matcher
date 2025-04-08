<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;

/** @internal */
final class ExceptionClassMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): ExceptionClassMatchCondition
    {
        return new ExceptionClassMatchCondition($capture->getExceptionClass());
    }
}
