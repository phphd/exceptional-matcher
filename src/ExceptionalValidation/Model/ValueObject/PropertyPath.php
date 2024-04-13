<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\ValueObject;

use function array_pop;
use function implode;
use function sprintf;

final class PropertyPath
{
    public function __construct(
        /** @var list<string> */
        private readonly array $items,
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function with(string $item): self
    {
        return new self([...$this->items, $item]);
    }

    public function at(int|string $key): self
    {
        $items = $this->items;

        $lastItem = array_pop($items);

        return new self([...$items, sprintf('%s[%s]', $lastItem, $key)]);
    }

    public function join(string $separator): string
    {
        return implode($separator, $this->items);
    }
}
