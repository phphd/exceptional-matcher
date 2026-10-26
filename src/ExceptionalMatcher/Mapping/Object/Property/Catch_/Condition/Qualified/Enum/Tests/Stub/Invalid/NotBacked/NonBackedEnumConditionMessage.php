<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Enum\Tests\Stub\Invalid\NotBacked;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use ValueError;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Enum\enum_value;

#[Try_]
final class NonBackedEnumConditionMessage
{
    #[Catch_(ValueError::class, from: [NonBackedStatus::class, 'from'], match: enum_value)]
    public mixed $status;
}
