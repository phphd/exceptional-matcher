<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Origin;

use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class ExceptionOriginMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $parent): ?MatchCondition
    {
        $origin = $catch->getFrom();

        if (null === $origin) {
            return null;
        }

        return new ExceptionOriginMatchCondition(...$origin);
    }
}
