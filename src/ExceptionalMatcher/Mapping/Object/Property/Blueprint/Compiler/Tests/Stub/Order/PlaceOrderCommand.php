<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order;

use DateTimeImmutable;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\InclusionRule;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\ProbeOf;
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
    #[ProbeOf(InclusionRule::NonFinalClassTypedPropertyIsIncluded, 'concrete class')]
    private Customer $customer;

    /** @psalm-suppress MissingPropertyType Left untyped, as legacy properties are */
    #[ProbeOf(InclusionRule::LooselyTypedPropertyIsIncluded)] // @phpstan-ignore typeCoverage.propertyTypeCoverage
    private $customerNote; // @phpstan-ignore missingType.property

    #[ProbeOf(InclusionRule::IterableTypedPropertyIsIncluded)]
    private OrderLines $lines;

    #[ProbeOf(InclusionRule::NonFinalClassTypedPropertyIsIncluded, 'abstract class')]
    private Discount $discount;

    /** @var list<string> */
    #[ProbeOf(InclusionRule::IterableTypedPropertyIsIncluded)]
    private array $couponCodes;

    /** @var iterable<string> */
    #[ProbeOf(InclusionRule::IterableTypedPropertyIsIncluded)]
    private iterable $giftCardCodes;

    /** @var Traversable<int,LoyaltyReward> */
    #[ProbeOf(InclusionRule::IterableTypedPropertyIsIncluded)]
    private Traversable $loyaltyRewards;

    #[ProbeOf(InclusionRule::ScalarTypedPropertyIsSkipped)]
    private int $loyaltyPointsToSpend;

    #[ProbeOf(InclusionRule::InterfaceTypedPropertyIsIncluded)]
    private PaymentMethod $paymentMethod;

    #[ProbeOf(InclusionRule::LooselyTypedPropertyIsIncluded)]
    private object $gatewayPayload;

    #[ProbeOf(InclusionRule::FinalExceptionMappedClassTypedPropertyIsIncluded)]
    private ShippingAddress $shippingAddress;

    #[ProbeOf(InclusionRule::EnumTypedPropertyIsSkipped)]
    private ShippingMethod $shippingMethod;

    #[ProbeOf(InclusionRule::BuiltInClassTypedPropertyIsSkipped, 'class')]
    private DateTimeImmutable $requestedDeliveryDate;

    #[ProbeOf(InclusionRule::BuiltInClassTypedPropertyIsSkipped, 'interface')]
    private Stringable $recipientPhone;

    #[ProbeOf(InclusionRule::FinalNonExceptionMappedClassTypedPropertyIsSkipped)]
    private Money $courierTip;

    #[ProbeOf(InclusionRule::LooselyTypedPropertyIsIncluded)]
    private mixed $metadata;
}
