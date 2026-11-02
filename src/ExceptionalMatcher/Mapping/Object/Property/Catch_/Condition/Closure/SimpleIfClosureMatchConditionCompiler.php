<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class SimpleIfClosureMatchConditionCompiler implements MatchConditionCompiler
{
    public function compile(ReflectionProperty $property, Catch_ $catch): ?SimpleIfClosureMatchConditionBlueprint
    {
        $if = $catch->getIf();

        if (null === $if) {
            return null;
        }

        Assert::count($if, 2);

        return new SimpleIfClosureMatchConditionBlueprint($if);
    }
}
