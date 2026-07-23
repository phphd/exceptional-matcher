<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite;

use Iterator;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
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

    public function compile(Catch_ $catch): CompositeMatchConditionBlueprint
    {
        // materialized eagerly: compiling a catch IS its validation - every statically
        // detectable mapping error must surface right here, not on the first bind
        return new CompositeMatchConditionBlueprint(iterator_to_array($this->conditionBlueprints($catch), false));
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
