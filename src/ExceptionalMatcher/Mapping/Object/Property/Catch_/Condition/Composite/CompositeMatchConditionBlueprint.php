<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionBlueprint<Throwable>
 */
final class CompositeMatchConditionBlueprint implements MatchConditionBlueprint
{
    public function __construct(
        /** @var iterable<MatchConditionBlueprint<Throwable>> */
        private readonly iterable $plans,
    ) {
    }

    public function bind(ExceptionMappingNode $node): CompositeMatchCondition
    {
        return new CompositeMatchCondition(new MemoizingIteratorAggregate($this->conditions($node)));
    }

    /** @return iterable<MatchConditionBlueprint<Throwable>> */
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
