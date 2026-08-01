<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionPlan;

use function is_a;

/** @api */
const exception_value = ExceptionValueMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValueException>
 * @implements MatchConditionPlan<ValueException>
 */
final class ExceptionValueMatchConditionCompiler implements MatchConditionCompiler, MatchConditionPlan
{
    /** @return MatchConditionPlan<ValueException> */
    public function compile(Catch_ $catch): MatchConditionPlan
    {
        if (!is_a($catch->getExceptionClass(), ValueException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ExceptionValueMatchCondition can only be used for exception classes that implement ValueException');
        }

        return $this;
    }

    public function bind(ExceptionMappingNode $rule): ExceptionValueMatchCondition
    {
        return new ExceptionValueMatchCondition($rule->getValue());
    }
}
