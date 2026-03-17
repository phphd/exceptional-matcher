<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function is_a;

/**
 * @internal
 *
 * @implements MatchConditionFactory<ValidationFailedException>
 */
final class ValidationFailedExceptionMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $parent): ValidationFailedExceptionMatchCondition
    {
        if (!is_a($catch->getExceptionClass(), ValidationFailedException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ValidationFailedExceptionMatchCondition can only be used for ValidationFailedException');
        }

        return new ValidationFailedExceptionMatchCondition($parent->getValue());
    }
}
