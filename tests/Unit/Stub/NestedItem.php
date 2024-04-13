<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\NestedIterableItemCapturedException;

#[ExceptionalValidation]
final class NestedItem
{
    #[ExceptionalValidation\Capture(NestedIterableItemCapturedException::class, 'oops', when: [self::class, 'matchesValue'])]
    private int $property;

    public function __construct(int $property)
    {
        $this->property = $property;
    }

    public function matchesValue(NestedIterableItemCapturedException $exception): bool
    {
        return $exception->getCode() === $this->property;
    }
}
