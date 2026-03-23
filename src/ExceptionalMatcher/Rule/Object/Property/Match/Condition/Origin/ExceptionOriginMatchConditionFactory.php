<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class ExceptionOriginMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $owner): ?MatchCondition
    {
        $origin = $catch->getFrom();

        if (null === $origin) {
            return null;
        }

        return new ExceptionOriginMatchCondition(...$origin);
    }
}
