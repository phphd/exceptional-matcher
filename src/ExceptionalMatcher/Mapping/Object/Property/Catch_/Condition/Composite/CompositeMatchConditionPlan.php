<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
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

    public function bind(ExceptionMappingNode $node): CompositeMatchCondition
    {
        return new CompositeMatchCondition(new ReusableIteratorAggregate($this->conditions($node)));
    }

    /** @return iterable<MatchConditionPlan<Throwable>> */
    public function getPlans(): iterable
    {
        return $this->plans;
    }

    /** @psalm-suppress UnusedForeachValue */
    public function hasPlans(): bool
    {
        /** @noinspection PhpLoopNeverIteratesInspection */
        foreach ($this->plans as $plan) {
            return true;
        }

        return false;
    }

    /** @return Iterator<MatchCondition<Throwable>> */
    private function conditions(ExceptionMappingNode $node): Iterator
    {
        foreach ($this->plans as $plan) {
            yield $plan->bind($node);
        }
    }
}
