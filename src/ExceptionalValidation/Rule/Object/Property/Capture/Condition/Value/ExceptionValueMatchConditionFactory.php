<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use LogicException;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function is_a;

/** @internal */
final class ExceptionValueMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): MatchCondition
    {
        $exceptionClass = $capture->getExceptionClass();

        if (!is_a($exceptionClass, ValueException::class, true) && !is_a($exceptionClass, ValidationFailedException::class, true)) {
            throw new LogicException('ExceptionValueMatchCondition could only be used for exception classes that implement ValueException, or those that are directly supported');
        }

        $value = $parent->getValue();

        return new ExceptionValueMatchCondition($value);
    }
}
