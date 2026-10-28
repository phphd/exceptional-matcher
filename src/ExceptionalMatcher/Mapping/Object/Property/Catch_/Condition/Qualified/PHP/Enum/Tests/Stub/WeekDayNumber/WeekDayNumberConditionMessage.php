<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\WeekDayNumber;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use ValueError;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\enum_value;

#[Try_]
final class WeekDayNumberConditionMessage
{
    public function __construct(
        #[Catch_(ValueError::class, from: [WeekDayNumber::class, 'from'], match: enum_value)]
        public mixed $weekDayNumber,
    ) {
    }
}
