<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\Invalid;

use DivisionByZeroError;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Undefined\undefined_condition;

#[Try_]
final class UndefinedConstantConditionMessage
{
    #[Catch_(DivisionByZeroError::class, match: undefined_condition)]
    private ?int $divisor = null;
}
