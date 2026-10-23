<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\Invalid;

use DivisionByZeroError;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Undefined\undefined_condition;

#[Try_]
final class UndefinedConstantConditionMessage
{
    #[Catch_(DivisionByZeroError::class, match: undefined_condition)]
    private ?int $divisor = null;
}
