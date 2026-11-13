<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart;

use RuntimeException;

final class OutOfStockProductException extends RuntimeException
{
    private function __construct(
        private readonly int $availableQuantity,
    ) {
        parent::__construct('Out of stock');
    }

    public static function create(int $availableQuantity): self
    {
        return new self($availableQuantity);
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }
}
