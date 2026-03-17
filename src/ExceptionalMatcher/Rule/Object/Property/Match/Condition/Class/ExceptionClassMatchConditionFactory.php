<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class ExceptionClassMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $parent): ExceptionClassMatchCondition
    {
        return new ExceptionClassMatchCondition($catch->getExceptionClass());
    }
}
