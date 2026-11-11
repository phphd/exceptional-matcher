<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\PropertyInclusionRule;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionMapper\Tests\Probe\ProbeOf;

#[Try_]
final class ShippingAddress
{
    #[ProbeOf(PropertyInclusionRule::CatchAttributedPropertyIsIncluded)]
    #[Catch_(InvalidPostalCodeException::class)]
    private string $postalCode;
}
