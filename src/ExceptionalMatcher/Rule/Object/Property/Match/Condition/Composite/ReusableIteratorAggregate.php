<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite;

use CachingIterator;
use Iterator;
use IteratorAggregate;
use Throwable;

/**
 * @internal
 *
 * @see SimpleCachingIteratorAggregate from loophp
 *
 * @template TKey
 * @template TValue
 *
 * @implements IteratorAggregate<TKey,TValue>
 */
final class ReusableIteratorAggregate implements IteratorAggregate
{
    /** @var CachingIterator<TKey,TValue,Iterator<TKey,TValue>> */
    private readonly CachingIterator $iterator;

    private ?Throwable $exception = null;

    /** @param Iterator<TKey,TValue> $iterator */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = new CachingIterator($iterator, CachingIterator::FULL_CACHE);
    }

    /** @return Iterator<TKey,TValue> */
    public function getIterator(): Iterator
    {
        /** @phpstan-ignore generator.keyType (CachingIterator::getCache() stub loses the TKey type) */
        yield from $this->iterator->getCache();

        if (null !== $this->exception) {
            throw $this->exception;
        }

        try {
            yield from $this->iterate();
        } catch (Throwable $e) {
            throw $this->exception = $e;
        }
    }

    private function iterate(): Iterator
    {
        while ($this->iterator->hasNext()) {
            $this->iterator->next();

            yield $this->iterator->key() => $this->iterator->current();
        }
    }
}
