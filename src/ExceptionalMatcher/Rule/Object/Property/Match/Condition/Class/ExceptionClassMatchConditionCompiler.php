<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\PreCompiledMatchConditionBlueprint;
use Throwable;

use function is_a;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class ExceptionClassMatchConditionCompiler implements MatchConditionCompiler
{
    /** @return PreCompiledMatchConditionBlueprint<Throwable> */
    public function compile(Catch_ $catch): PreCompiledMatchConditionBlueprint
    {
        $exceptionClass = $catch->getExceptionClass();

        if (!is_a($exceptionClass, Throwable::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('Exception class condition should only be used for exception classes that implement Throwable');
        }

        $condition = new ExceptionClassMatchCondition($exceptionClass);

        return new PreCompiledMatchConditionBlueprint($condition);
    }
}
