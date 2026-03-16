<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedItemMatchedException;

#[ExceptionalValidation]
final class NestedItem
{
    public function __construct(
        #[Catch_(NestedItemMatchedException::class, 'oops', when: [self::class, 'matchesValue'])]
        private readonly int $property,
    ) {
    }

    /** @api */
    public function matchesValue(NestedItemMatchedException $exception): bool
    {
        return $exception->getCode() === $this->property;
    }
}
