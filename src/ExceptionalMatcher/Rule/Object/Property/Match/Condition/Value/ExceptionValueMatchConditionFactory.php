<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;

use function is_a;

/** @api */
const exception_value = ExceptionValueMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionFactory<ValueException>
 */
final class ExceptionValueMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $owner): ExceptionValueMatchCondition
    {
        if (!is_a($catch->getExceptionClass(), ValueException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ExceptionValueMatchCondition can only be used for exception classes that implement ValueException');
        }

        return new ExceptionValueMatchCondition($owner->getValue());
    }
}
