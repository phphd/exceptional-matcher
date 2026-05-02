<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\NotBacked;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use ValueError;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\enum_value;

#[Try_]
final class NonBackedEnumConditionMessage
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(ValueError::class, from: [NonBackedStatus::class, 'from'], match: enum_value)]
        public mixed $status,
    ) {
    }
}
