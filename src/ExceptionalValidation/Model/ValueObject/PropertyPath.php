<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\ValueObject;

use function implode;

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

    public function join(string $separator): string
    {
        return implode($separator, $this->items);
    }
}
