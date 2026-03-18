<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\NestedItemMatchedException;

#[Try_]
final class NestedItem
{
    public function __construct(
        #[Catch_(NestedItemMatchedException::class, 'oops', if: [self::class, 'matchesValue'])]
        private readonly int $property,
    ) {
    }

    /** @api */
    public function matchesValue(NestedItemMatchedException $exception): bool
    {
        return $exception->getCode() === $this->property;
    }
}
