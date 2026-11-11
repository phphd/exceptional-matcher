<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Tests\Unit\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\NestedItemMatchedException;

#[Try_]
final class NestedItem
{
    public function __construct(
        #[Catch_(NestedItemMatchedException::class, if: [self::class, 'matchesValue'], message: 'oops')]
        private readonly int $property,
    ) {
    }

    /** @api */
    public function matchesValue(NestedItemMatchedException $exception): bool
    {
        return $exception->getCode() === $this->property;
    }
}
