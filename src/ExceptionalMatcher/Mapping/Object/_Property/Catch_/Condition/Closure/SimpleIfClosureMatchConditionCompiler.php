<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Closure;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\_Compiler\MatchConditionCompiler;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class SimpleIfClosureMatchConditionCompiler implements MatchConditionCompiler
{
    public function compile(Catch_ $catch): ?SimpleIfClosureMatchConditionPlan
    {
        $if = $catch->getIf();

        if (null === $if) {
            return null;
        }

        Assert::count($if, 2);

        return new SimpleIfClosureMatchConditionPlan($if);
    }
}
