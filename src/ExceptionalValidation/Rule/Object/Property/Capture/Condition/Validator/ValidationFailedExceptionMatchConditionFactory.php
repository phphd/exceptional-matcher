<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator;

use LogicException;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function is_a;

/**
 * @internal
 *
 * @implements MatchConditionFactory<ValidationFailedException>
 */
final class ValidationFailedExceptionMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): ValidationFailedExceptionMatchCondition
    {
        if (!is_a($capture->getExceptionClass(), ValidationFailedException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ValidationFailedExceptionMatchCondition can only be used for ValidationFailedException');
        }

        return new ValidationFailedExceptionMatchCondition($parent->getValue());
    }
}
