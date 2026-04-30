<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\WeekDay\WeekDay;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use RuntimeException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\enum_value;

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
