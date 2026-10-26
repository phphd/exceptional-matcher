<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests\Stub\Invalid;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use RuntimeException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\exception_value;

#[Try_]
final class NonValueExceptionClassMessage
{
    #[Catch_(RuntimeException::class, match: exception_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    public mixed $value;
}
