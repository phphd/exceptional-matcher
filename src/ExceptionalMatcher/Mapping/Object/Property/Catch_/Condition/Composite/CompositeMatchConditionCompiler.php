<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\entos\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\entos\Compiler\MatchConditionPlan;
use Throwable;

use function iterator_to_array;

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

    public function compile(Catch_ $catch): CompositeMatchConditionPlan
    {
        // materialized eagerly: compiling a catch IS its validation - every statically
        // detectable mapping error must surface right here, not on the first bind
        return new CompositeMatchConditionPlan(iterator_to_array($this->conditionPlans($catch), false));
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
