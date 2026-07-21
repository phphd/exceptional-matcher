<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
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
        private readonly iterable $blueprints,
    ) {
    }

    public function bind(MappingRule $rule): CompositeMatchCondition
    {
        return new CompositeMatchCondition(new ReusableIteratorAggregate($this->conditions($rule)));
    }

    /** @return Iterator<MatchCondition<Throwable>> */
    private function conditions(MappingRule $rule): Iterator
    {
        foreach ($this->blueprints as $blueprint) {
            yield $blueprint->bind($rule);
        }
    }
}
