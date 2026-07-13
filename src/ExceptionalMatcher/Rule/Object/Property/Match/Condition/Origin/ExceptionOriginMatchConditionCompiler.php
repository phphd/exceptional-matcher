<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\PreCompiledMatchConditionBlueprint;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class ExceptionOriginMatchConditionCompiler implements MatchConditionCompiler
{
    /** @return ?PreCompiledMatchConditionBlueprint<Throwable> */
    public function compile(Catch_ $catch): ?PreCompiledMatchConditionBlueprint
    {
        $origin = $catch->getFrom();

        if (null === $origin) {
            return null;
        }

        $condition = new ExceptionOriginMatchCondition(...$origin);

        return new PreCompiledMatchConditionBlueprint($condition);
    }
}
