<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Line;

use ArrayIterator;
use IteratorAggregate;

/** @implements IteratorAggregate<int,OrderLine> */
final class OrderLines implements IteratorAggregate
{
    /** @var array<int,OrderLine> */
    private array $lines = [];

    /** @return ArrayIterator<int,OrderLine> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->lines);
    }
}
