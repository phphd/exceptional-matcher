<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\Tests\Stub\Priority\DeliveryPriority;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use ValueError;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\enum_value;

#[Try_]
final class SetDeliveryPriorityCommand
{
    public function __construct(
        #[Catch_(ValueError::class, from: [DeliveryPriority::class, 'from'], match: enum_value)]
        public mixed $priority,
    ) {
    }
}
