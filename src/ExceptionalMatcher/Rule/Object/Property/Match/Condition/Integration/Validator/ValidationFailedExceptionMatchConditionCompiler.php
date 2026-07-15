<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Validator;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function is_a;

/** @api */
const validated_value = ValidationFailedExceptionMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValidationFailedException>
 * @implements MatchConditionBlueprint<ValidationFailedException>
 */
final class ValidationFailedExceptionMatchConditionCompiler implements MatchConditionCompiler, MatchConditionBlueprint
{
    /** @return MatchConditionBlueprint<ValidationFailedException> */
    public function compile(Catch_ $catch): MatchConditionBlueprint
    {
        if (!is_a($catch->getExceptionClass(), ValidationFailedException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ValidationFailedExceptionMatchCondition can only be used for ValidationFailedException');
        }

        return $this;
    }

    public function bind(MatchingRule $rule): ValidationFailedExceptionMatchCondition
    {
        return new ValidationFailedExceptionMatchCondition($rule->getValue());
    }
}
