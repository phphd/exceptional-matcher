<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Composite;

use Iterator;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionCompiler;
use ReflectionProperty;
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

    public function compile(ReflectionProperty $property, Catch_ $catch): ?CompositeMatchConditionBlueprint
    {
        $print = new CompositeMatchConditionBlueprint(new MemoizingIteratorAggregate($this->conditionPrints($property, $catch)));

        if (!$print->hasPrints()) {
            return null;
        }

        return $print;
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return Iterator<MatchConditionBlueprint<Throwable>>
     */
    private function conditionPrints(ReflectionProperty $property, Catch_ $catch): Iterator
    {
        foreach ($this->compilers as $compiler) {
            $print = $compiler->compile($property, $catch);

            if (null !== $print) {
                yield $print;
            }
        }
    }
}
