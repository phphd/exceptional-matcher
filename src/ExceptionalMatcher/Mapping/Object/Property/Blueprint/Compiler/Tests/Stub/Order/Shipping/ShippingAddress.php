<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\InclusionRule;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\ProbeOf;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class ShippingAddress
{
    #[ProbeOf(InclusionRule::CatchAttributedPropertyIsIncluded)]
    #[Catch_(InvalidPostalCodeException::class)]
    private string $postalCode;
}
