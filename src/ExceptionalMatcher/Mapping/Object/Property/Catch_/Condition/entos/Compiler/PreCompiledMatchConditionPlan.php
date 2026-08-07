<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\entos\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 *
 * @implements MatchConditionPlan<T>
 */
final class PreCompiledMatchConditionPlan implements MatchConditionPlan
{
    public function __construct(
        /** @var MatchCondition<T> */
        private readonly MatchCondition $condition,
    ) {
    }

    /** @return MatchCondition<T> */
    public function bind(ExceptionMappingNode $rule): MatchCondition
    {
        return $this->condition;
    }
}
