<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Enum\Tests\Stub\WeekDayNumber;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use ValueError;

use const PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Enum\enum_value;

#[Try_]
final class WeekDayNumberConditionMessage
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(ValueError::class, from: [WeekDayNumber::class, 'from'], match: enum_value)]
        public mixed $weekDayNumber,
    ) {
    }
}
