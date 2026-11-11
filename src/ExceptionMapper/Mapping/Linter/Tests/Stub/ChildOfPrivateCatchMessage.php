<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub;

use DivisionByZeroError;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;

#[Try_]
final class ChildOfPrivateCatchMessage extends ParentPrivateCatchMessage
{
    #[Catch_(DivisionByZeroError::class)]
    private ?int $childDivisor = null;
}
