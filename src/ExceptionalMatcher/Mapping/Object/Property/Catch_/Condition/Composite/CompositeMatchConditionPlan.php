<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\ento\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionPlan<Throwable>
 */
final class CompositeMatchConditionPlan implements MatchConditionPlan
{
    public function __construct(
        /** @var iterable<MatchConditionPlan<Throwable>> */
        private readonly iterable $plans,
    ) {
    }

    public function bind(ExceptionMappingNode $rule): CompositeMatchCondition
    {
        return new CompositeMatchCondition(new ReusableIteratorAggregate($this->conditions($rule)));
    }

    /** @return Iterator<MatchCondition<Throwable>> */
    private function conditions(ExceptionMappingNode $rule): Iterator
    {
        foreach ($this->plans as $plan) {
            yield $plan->bind($rule);
        }
    }
}
