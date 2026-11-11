<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub;

use DivisionByZeroError;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;

/** @psalm-suppress ClassMustBeFinal deliberately extensible - the child inherits its private property */
class ParentPrivateCatchMessage
{
    #[Catch_(DivisionByZeroError::class)]
    private ?int $parentDivisor = null;
}
