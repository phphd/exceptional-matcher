<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Enum\Tests\Stub\Invalid;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Enum\Tests\Stub\WeekDay\WeekDay;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use RuntimeException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Enum\enum_value;

#[Try_]
final class NonEnumExceptionClassConditionMessage
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(RuntimeException::class, from: [WeekDay::class, 'from'], match: enum_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
        public mixed $weekDay,
    ) {
    }
}
