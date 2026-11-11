<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Method;

enum DeliveryMethod: string
{
    case PICKUP = 'pickup';
    case COURIER = 'courier';
    case LOCKER = 'locker';
}
