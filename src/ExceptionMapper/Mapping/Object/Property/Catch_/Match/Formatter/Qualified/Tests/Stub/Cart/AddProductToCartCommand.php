<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;

#[Try_]
final class AddProductToCartCommand
{
    public function __construct(
        public readonly string $productId,
        #[Catch_(OutOfStockProductException::class, format: out_of_stock)]
        public readonly int $quantity,
    ) {
    }
}
