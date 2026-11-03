<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order;

use DateTimeImmutable;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Customer\Customer;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Line\OrderLines;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Payment\PaymentMethod;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Pricing\Discount;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Pricing\LoyaltyReward;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping\ShippingAddress;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping\ShippingMethod;
use Stringable;
use Traversable;

final class PlaceOrderCommand
{
    private Customer $customer;

    /** @psalm-suppress MissingPropertyType Left untyped, as legacy properties are */
    private $customerNote; // @phpstan-ignore missingType.property, typeCoverage.propertyTypeCoverage

    private OrderLines $lines;

    private Discount $discount;

    /** @var list<string> */
    private array $couponCodes;

    /** @var iterable<string> */
    private iterable $giftCardCodes;

    /** @var Traversable<int,LoyaltyReward> */
    private Traversable $loyaltyRewards;

    private int $loyaltyPointsToSpend;

    private PaymentMethod $paymentMethod;

    private object $gatewayPayload;

    private ShippingAddress $shippingAddress;

    private ShippingMethod $shippingMethod;

    private DateTimeImmutable $requestedDeliveryDate;

    private Stringable $recipientPhone;

    private Money $courierTip;

    private mixed $metadata;
}
