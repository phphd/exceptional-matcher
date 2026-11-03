<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Line\OrderLines;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Pricing\LoyaltyReward;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping\ShippingAddress;
use Traversable;

final class PlaceOrderCommand
{
    /** @psalm-suppress MissingPropertyType Left untyped, as legacy properties are */
    private $customerNote; // @phpstan-ignore missingType.property, typeCoverage.propertyTypeCoverage

    private OrderLines $lines;

    /** @var list<string> */
    private array $couponCodes;

    /** @var iterable<string> */
    private iterable $giftCardCodes;

    /** @var Traversable<int,LoyaltyReward> */
    private Traversable $loyaltyRewards;

    private object $gatewayPayload;

    private ShippingAddress $shippingAddress;

    private mixed $metadata;
}
