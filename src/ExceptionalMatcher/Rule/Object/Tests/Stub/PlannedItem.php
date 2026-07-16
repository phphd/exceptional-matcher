<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;

#[Try_]
final class PlannedItem
{
    public function __construct(
        #[Catch_(NestedStubException::class, message: 'nested.oops')]
        private readonly mixed $itemValue,
    ) {
    }
}
