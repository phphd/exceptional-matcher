<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub;

use DivisionByZeroError;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class UnregisteredFormatterMessage
{
    #[Catch_(DivisionByZeroError::class, format: UnregisteredFormatter::class)]
    private ?int $divisor = null;
}
