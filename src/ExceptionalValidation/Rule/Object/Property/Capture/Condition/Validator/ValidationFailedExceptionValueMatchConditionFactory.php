<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator;

use LogicException;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function is_a;

/** @internal */
final class ValidationFailedExceptionValueMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): ValidationFailedExceptionValueMatchCondition
    {
        if (!is_a($capture->getExceptionClass(), ValidationFailedException::class, true)) {
            throw new LogicException('ValidationFailedExceptionValueMatchCondition can only be used for ValidationFailedException');
        }

        return new ValidationFailedExceptionValueMatchCondition($parent->getValue());
    }
}
