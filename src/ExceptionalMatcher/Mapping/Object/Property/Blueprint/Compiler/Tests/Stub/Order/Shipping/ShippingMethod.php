<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping;

enum ShippingMethod
{
    case Standard;
    case Express;
    case Pickup;
}
