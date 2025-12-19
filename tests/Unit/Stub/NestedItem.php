<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedItemCapturedException;

#[ExceptionalValidation]
final class NestedItem
{
    public function __construct(
        #[Capture(NestedItemCapturedException::class, 'oops', when: [self::class, 'matchesValue'])]
        private readonly int $property,
    ) {
    }

    /** @api */
    public function matchesValue(NestedItemCapturedException $exception): bool
    {
        return $exception->getCode() === $this->property;
    }
}
