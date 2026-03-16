<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Closure;

use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class ClosureMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $parent): ?MatchCondition
    {
        $when = $catch->getWhen();

        if (null === $when) {
            return null;
        }

        Assert::methodExists(...$when);

        $object = $parent->getEnclosingObject();

        if ($when[0] === $object::class) {
            $when = [$object, $when[1]];
        }

        /** @phpstan-ignore callable.nonCallable */
        return new ClosureMatchCondition($when(...));
    }
}
