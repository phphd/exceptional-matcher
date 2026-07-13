<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 *
 * @implements MatchConditionBlueprint<T>
 */
final class PreCompiledMatchConditionBlueprint implements MatchConditionBlueprint
{
    public function __construct(
        /** @var MatchCondition<T> */
        private readonly MatchCondition $condition,
    ) {
    }

    /** @return MatchCondition<T> */
    public function bind(MatchingRule $rule): MatchCondition
    {
        return $this->condition;
    }
}
