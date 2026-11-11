<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Priority;

enum DeliveryPriority: int
{
    case STANDARD = 1;
    case EXPRESS = 2;
    case OVERNIGHT = 3;
}
