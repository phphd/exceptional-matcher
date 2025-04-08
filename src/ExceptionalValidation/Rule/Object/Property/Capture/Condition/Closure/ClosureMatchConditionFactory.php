<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;

/** @internal */
final class ClosureMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): ?MatchCondition
    {
        $when = $capture->getWhen();

        if (null === $when) {
            return null;
        }

        $object = $parent->getEnclosingObject();

        if ($when[0] === $object::class) {
            $when = [$object, $when[1]];
        }

        /** @phpstan-ignore callable.nonCallable */
        return new ClosureMatchCondition($when(...));
    }
}
