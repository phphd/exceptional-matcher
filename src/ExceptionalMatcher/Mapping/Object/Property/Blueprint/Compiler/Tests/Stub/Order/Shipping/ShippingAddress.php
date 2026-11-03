<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\PropertyInclusionRule;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Tests\Probe\ProbeOf;

#[Try_]
final class ShippingAddress
{
    #[ProbeOf(PropertyInclusionRule::CatchAttributedPropertyIsIncluded)]
    #[Catch_(InvalidPostalCodeException::class)]
    private string $postalCode;
}
