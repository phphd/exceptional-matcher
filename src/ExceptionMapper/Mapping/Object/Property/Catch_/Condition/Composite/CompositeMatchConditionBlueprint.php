<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite;

use Iterator;
use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Throwable;

use function iterator_count;

/**
 * @internal
 *
 * @implements MatchConditionBlueprint<Throwable>
 */
final class CompositeMatchConditionBlueprint implements MatchConditionBlueprint
{
    public function __construct(
        /** @var iterable<MatchConditionBlueprint<Throwable>> */
        private readonly iterable $prints,
    ) {
    }

    public function bind(ExceptionMappingNode $node): CompositeMatchCondition
    {
        return new CompositeMatchCondition(new MemoizingIteratorAggregate($this->conditions($node)));
    }

    /** @psalm-suppress UnusedForeachValue */
    public function hasPrints(): bool
    {
        /** @noinspection PhpLoopNeverIteratesInspection */
        foreach ($this->prints as $print) {
            return true;
        }

        return false;
    }

    public function forceCompleteCompilation(): int
    {
        return iterator_count($this->getLeafPrintsDFS());
    }

    /** @return Iterator<MatchConditionBlueprint<Throwable>> */
    private function getLeafPrintsDFS(): Iterator
    {
        foreach ($this->prints as $print) {
            if ($print instanceof self) {
                yield from $print->getLeafPrintsDFS();
            } else {
                yield $print;
            }
        }
    }

    /** @return Iterator<MatchCondition<Throwable>> */
    private function conditions(ExceptionMappingNode $node): Iterator
    {
        foreach ($this->prints as $print) {
            yield $print->bind($node);
        }
    }
}
