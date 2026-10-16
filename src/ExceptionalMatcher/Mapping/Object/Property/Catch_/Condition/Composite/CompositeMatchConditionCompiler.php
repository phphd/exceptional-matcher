<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\PreCompiledMatchConditionPlan;
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

    /** @return CompositeMatchConditionPlan|PreCompiledMatchConditionPlan<Throwable> */
    public function compile(Catch_ $catch): CompositeMatchConditionPlan|PreCompiledMatchConditionPlan
    {
        $plan = new CompositeMatchConditionPlan(new ReusableIteratorAggregate($this->conditionPlans($catch)));

        if (!$plan->hasPlans()) {
            return new PreCompiledMatchConditionPlan(new FalseCondition());
        }

        return $plan;
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return Iterator<MatchConditionPlan<Throwable>>
     */
    private function conditionPlans(Catch_ $catch): Iterator
    {
        foreach ($this->compilers as $compiler) {
            $plan = $compiler->compile($catch);

            if (null === $plan) {
                continue;
            }

            yield $plan;
        }
    }
}
