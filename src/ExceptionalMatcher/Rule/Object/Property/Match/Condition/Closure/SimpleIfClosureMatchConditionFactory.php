<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class SimpleIfClosureMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $owner): ?MatchCondition
    {
        $if = $catch->getIf();

        if (null === $if) {
            return null;
        }

        Assert::methodExists(...$if);

        $object = $owner->getEnclosingObject();

        if ($if[0] === $object::class) {
            $if = [$object, $if[1]];
        }

        /** @phpstan-ignore callable.nonCallable */
        return new ClosureMatchCondition($if(...));
    }
}
