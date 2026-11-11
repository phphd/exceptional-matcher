<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order;

use DateTimeImmutable;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\PropertyInclusionRule;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Customer\Customer;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Line\OrderLines;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Payment\PaymentMethod;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Pricing\Discount;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Pricing\LoyaltyReward;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping\ShippingAddress;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping\ShippingMethod;
use PhPhD\ExceptionMapper\Tests\Probe\ProbeOf;
use Stringable;
use Traversable;

final class PlaceOrderCommand
{
    #[ProbeOf(PropertyInclusionRule::NonFinalClassTypedPropertyIsIncluded, 'concrete class')]
    private Customer $customer;

    /** @psalm-suppress MissingPropertyType Left untyped, as legacy properties are */
    #[ProbeOf(PropertyInclusionRule::LooselyTypedPropertyIsIncluded)] // @phpstan-ignore typeCoverage.propertyTypeCoverage
    private $customerNote; // @phpstan-ignore missingType.property

    #[ProbeOf(PropertyInclusionRule::IterableTypedPropertyIsIncluded)]
    private OrderLines $lines;

    #[ProbeOf(PropertyInclusionRule::NonFinalClassTypedPropertyIsIncluded, 'abstract class')]
    private Discount $discount;

    /** @var list<string> */
    #[ProbeOf(PropertyInclusionRule::IterableTypedPropertyIsIncluded)]
    private array $couponCodes;

    /** @var iterable<string> */
    #[ProbeOf(PropertyInclusionRule::IterableTypedPropertyIsIncluded)]
    private iterable $giftCardCodes;

    /** @var Traversable<int,LoyaltyReward> */
    #[ProbeOf(PropertyInclusionRule::IterableTypedPropertyIsIncluded)]
    private Traversable $loyaltyRewards;

    #[ProbeOf(PropertyInclusionRule::ScalarTypedPropertyIsSkipped)]
    private int $loyaltyPointsToSpend;

    #[ProbeOf(PropertyInclusionRule::InterfaceTypedPropertyIsIncluded)]
    private PaymentMethod $paymentMethod;

    #[ProbeOf(PropertyInclusionRule::LooselyTypedPropertyIsIncluded)]
    private object $gatewayPayload;

    #[ProbeOf(PropertyInclusionRule::FinalExceptionMappedClassTypedPropertyIsIncluded)]
    private ShippingAddress $shippingAddress;

    #[ProbeOf(PropertyInclusionRule::EnumTypedPropertyIsSkipped)]
    private ShippingMethod $shippingMethod;

    #[ProbeOf(PropertyInclusionRule::BuiltInClassTypedPropertyIsSkipped, 'class')]
    private DateTimeImmutable $requestedDeliveryDate;

    #[ProbeOf(PropertyInclusionRule::BuiltInClassTypedPropertyIsSkipped, 'interface')]
    private Stringable $recipientPhone;

    #[ProbeOf(PropertyInclusionRule::FinalNonExceptionMappedClassTypedPropertyIsSkipped)]
    private Money $courierTip;

    #[ProbeOf(PropertyInclusionRule::LooselyTypedPropertyIsIncluded)]
    private mixed $metadata;
}
