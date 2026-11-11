<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable\Tests\Stub;

use ArrayObject;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\NestedItem;

#[Try_]
final class RootObject
{
    private array $nestedArrayItems;

    private ArrayObject $nestedIterableItems; // @phpstan-ignore missingType.generics

    public static function create(): self
    {
        return new self();
    }

    public function withNestedArrayItems(array $array): self
    {
        $message = clone $this;
        $message->nestedArrayItems = $array;

        return $message;
    }

    /** @param ArrayObject<array-key,NestedItem> $items */
    public function withNestedIterableItems(ArrayObject $items): self
    {
        $message = clone $this;
        $message->nestedIterableItems = $items;

        return $message;
    }
}
