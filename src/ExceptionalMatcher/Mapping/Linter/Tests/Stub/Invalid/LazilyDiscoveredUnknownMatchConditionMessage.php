<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\Invalid;

use DivisionByZeroError;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

/** @psalm-suppress ArgumentTypeCoercion specifically test an unregistered match condition */
#[Try_]
final class LazilyDiscoveredUnknownMatchConditionMessage
{
    // The unknown `match:` condition print would fail lazily if it's at the third position,
    // beyond the eager look-ahead of print compilation - `from:` is needed here.
    #[Catch_(DivisionByZeroError::class, from: 'intdiv', match: 'unknown_condition')] // @phpstan-ignore argument.type (specifically test an unregistered match condition)
    public ?int $divisor = null;
}
