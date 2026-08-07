<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class PlannedItem
{
    public function __construct(
        #[Catch_(NestedStubException::class, message: 'nested.oops')]
        private readonly mixed $itemValue,
    ) {
    }
}
