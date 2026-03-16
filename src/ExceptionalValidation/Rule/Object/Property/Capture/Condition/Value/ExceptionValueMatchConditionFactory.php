<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use LogicException;
use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;

use function is_a;

/**
 * @internal
 *
 * @implements MatchConditionFactory<ValueException>
 */
final class ExceptionValueMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, CaptureRule $parent): ExceptionValueMatchCondition
    {
        if (!is_a($catch->getExceptionClass(), ValueException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ExceptionValueMatchCondition can only be used for exception classes that implement ValueException');
        }

        return new ExceptionValueMatchCondition($parent->getValue());
    }
}
