<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Invalid;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\WeekDay\WeekDay;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use ValueError;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\enum_value;

#[Try_]
final class InvalidEnumFromMethodConditionMessage
{
    #[Catch_(ValueError::class, from: [WeekDay::class, 'tryFrom'], match: enum_value)]
    public mixed $weekDay;
}
