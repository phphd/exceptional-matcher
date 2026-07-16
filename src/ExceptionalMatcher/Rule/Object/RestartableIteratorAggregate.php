<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use Closure;
use Iterator;
use IteratorAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use Throwable;

/**
 * Memoizes the items of the underlying iterator once it has been fully traversed, yet forgets
 * everything as soon as an iteration fails: a generator that has thrown is closed for good, so
 * replaying its cache would silently truncate the sequence - instead, the next iteration starts
 * over from a freshly created iterator.
 *
 * @internal
 *
 * @template TKey
 * @template TValue
 *
 * @implements IteratorAggregate<TKey,TValue>
 */
final class RestartableIteratorAggregate implements IteratorAggregate
{
    /** @var ?ReusableIteratorAggregate<TKey,TValue> */
    private ?ReusableIteratorAggregate $memoizedIterator = null;

    public function __construct(
        /** @var Closure():Iterator<TKey,TValue> */
        private readonly Closure $createIterator,
    ) {
    }

    /** @return Iterator<TKey,TValue> */
    public function getIterator(): Iterator
    {
        $iterator = $this->memoizedIterator ??= new ReusableIteratorAggregate(($this->createIterator)());

        try {
            yield from $iterator->getIterator();
        } catch (Throwable $exception) {
            $this->memoizedIterator = null;

            throw $exception;
        }
    }
}
