<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Enum\Tests\Stub\Invalid;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Enum\Tests\Stub\WeekDay\WeekDay;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use RuntimeException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Enum\enum_value;

#[Try_]
final class NonEnumExceptionClassMessage
{
    #[Catch_(RuntimeException::class, from: [WeekDay::class, 'from'], match: enum_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    public mixed $weekDay;
}
