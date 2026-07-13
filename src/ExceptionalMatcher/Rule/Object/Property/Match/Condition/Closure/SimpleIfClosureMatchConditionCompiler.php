<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class SimpleIfClosureMatchConditionCompiler implements MatchConditionCompiler
{
    public function compile(Catch_ $catch): ?SimpleIfClosureMatchConditionBlueprint
    {
        $if = $catch->getIf();

        if (null === $if) {
            return null;
        }

        Assert::count($if, 2);

        return new SimpleIfClosureMatchConditionBlueprint($if);
    }
}
