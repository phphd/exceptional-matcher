<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;

use function is_a;

/** @api */
const exception_value = ExceptionValueMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValueException>
 * @implements MatchConditionBlueprint<ValueException>
 */
final class ExceptionValueMatchConditionCompiler implements MatchConditionCompiler, MatchConditionBlueprint
{
    /** @return MatchConditionBlueprint<ValueException> */
    public function compile(Catch_ $catch): MatchConditionBlueprint
    {
        if (!is_a($catch->getExceptionClass(), ValueException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('ExceptionValueMatchCondition can only be used for exception classes that implement ValueException');
        }

        return $this;
    }

    public function bind(MappingRule $rule): ExceptionValueMatchCondition
    {
        return new ExceptionValueMatchCondition($rule->getValue());
    }
}
