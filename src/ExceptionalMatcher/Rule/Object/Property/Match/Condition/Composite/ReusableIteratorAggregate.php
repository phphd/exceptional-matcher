<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite;

use CachingIterator;
use Iterator;
use IteratorAggregate;

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

        while ($this->iterator->hasNext()) {
            $this->iterator->next();

            yield $this->iterator->key() => $this->iterator->current();
        }
    }
}
