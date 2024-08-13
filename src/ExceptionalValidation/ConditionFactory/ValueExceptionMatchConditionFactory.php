<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\ConditionFactory;

use LogicException;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Model\Condition\Exception\ValueException;
use PhPhD\ExceptionalValidation\Model\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Model\Condition\ValueExceptionMatchCondition;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;

use function is_a;

/** @internal */
final class ValueExceptionMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): MatchCondition
    {
        $exceptionClass = $capture->getExceptionClass();

        if (!is_a($exceptionClass, ValueException::class, true)) {
            throw new LogicException('Invalid value condition could only be used for exception class that implements ValueException');
        }

        $value = $parent->getValue();

        return new ValueExceptionMatchCondition($value);
    }
}
