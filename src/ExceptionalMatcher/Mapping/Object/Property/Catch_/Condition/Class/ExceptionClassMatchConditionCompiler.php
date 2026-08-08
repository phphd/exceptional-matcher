<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Class;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\PreCompiledMatchConditionPlan;
use Throwable;

use function is_a;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class ExceptionClassMatchConditionCompiler implements MatchConditionCompiler
{
    /** @return PreCompiledMatchConditionPlan<Throwable> */
    public function compile(Catch_ $catch): PreCompiledMatchConditionPlan
    {
        $exceptionClass = $catch->getExceptionClass();

        if (!is_a($exceptionClass, Throwable::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('Exception class condition should only be used for exception classes that implement Throwable');
        }

        $condition = new ExceptionClassMatchCondition($exceptionClass);

        return new PreCompiledMatchConditionPlan($condition);
    }
}
