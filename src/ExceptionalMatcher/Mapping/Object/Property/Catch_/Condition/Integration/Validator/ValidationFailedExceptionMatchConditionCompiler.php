<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function is_a;

/** @api */
const validated_value = ValidationFailedExceptionMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValidationFailedException>
 * @implements MatchConditionPlan<ValidationFailedException>
 */
final class ValidationFailedExceptionMatchConditionCompiler implements MatchConditionCompiler, MatchConditionPlan
{
    /** @return MatchConditionPlan<ValidationFailedException> */
    public function compile(Catch_ $catch): MatchConditionPlan
    {
        if (!is_a($catch->getExceptionClass(), ValidationFailedException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ValidationFailedExceptionMatchCondition can only be used for ValidationFailedException');
        }

        return $this;
    }

    public function bind(ExceptionMappingNode $rule): ValidationFailedExceptionMatchCondition
    {
        return new ValidationFailedExceptionMatchCondition($rule->getValue());
    }
}
