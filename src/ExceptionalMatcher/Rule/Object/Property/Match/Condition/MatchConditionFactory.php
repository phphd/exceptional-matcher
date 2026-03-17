<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use Throwable;

/**
 * @internal - this might be exposed as an api in some future versions
 *
 * @template T of Throwable
 */
interface MatchConditionFactory
{
    /**
     * @param Catch_<T,T> $catch
     *
     * @return ?MatchCondition<T>
     */
    public function getCondition(Catch_ $catch, MatchingRule $parent): ?MatchCondition;
}
