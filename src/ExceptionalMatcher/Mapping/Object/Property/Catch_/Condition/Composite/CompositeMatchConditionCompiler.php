<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\PreCompiledMatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class CompositeMatchConditionCompiler implements MatchConditionCompiler
{
    /** @api */
    public function __construct(
        /** @var iterable<MatchConditionCompiler<Throwable>> */
        private readonly iterable $compilers,
    ) {
    }

    /** @return CompositeMatchConditionBlueprint|PreCompiledMatchConditionBlueprint<Throwable> */
    public function compile(Catch_ $catch): CompositeMatchConditionBlueprint|PreCompiledMatchConditionBlueprint
    {
        $plan = new CompositeMatchConditionBlueprint(new MemoizingIteratorAggregate($this->conditionPlans($catch)));

        if (!$plan->hasPlans()) {
            return new PreCompiledMatchConditionBlueprint(new FalseCondition());
        }

        return $plan;
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return Iterator<MatchConditionBlueprint<Throwable>>
     */
    private function conditionPlans(Catch_ $catch): Iterator
    {
        foreach ($this->compilers as $compiler) {
            $plan = $compiler->compile($catch);

            if (null !== $plan) {
                yield $plan;
            }
        }
    }
}
