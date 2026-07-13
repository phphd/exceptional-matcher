<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
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

    public function compile(Catch_ $catch): CompositeMatchConditionBlueprint
    {
        return new CompositeMatchConditionBlueprint(new ReusableIteratorAggregate($this->conditionBlueprints($catch)));
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return Iterator<MatchConditionBlueprint<Throwable>>
     */
    private function conditionBlueprints(Catch_ $catch): Iterator
    {
        foreach ($this->compilers as $compiler) {
            $blueprint = $compiler->compile($catch);

            if (null === $blueprint) {
                continue;
            }

            yield $blueprint;
        }
    }
}
