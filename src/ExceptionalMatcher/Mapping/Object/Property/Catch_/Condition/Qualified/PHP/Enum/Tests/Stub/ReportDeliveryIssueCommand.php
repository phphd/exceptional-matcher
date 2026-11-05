<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\EnumValueMatchConditionCompilerUnitTest;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Issue\DeliveryIssue;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Method\DeliveryMethod;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Priority\DeliveryPriority;
use RuntimeException;
use ValueError;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\enum_value;

final class ReportDeliveryIssueCommand
{
    /** @see EnumValueMatchConditionCompilerUnitTest::testCompilesWhenPropertyTypeCoversIntOrString() */
    #[Catch_(ValueError::class, from: DeliveryPriority::class, match: enum_value)]
    private int|string $reshipmentPriority;

    /** @see EnumValueMatchConditionCompilerUnitTest::testThrowsWhenPropertyTypeDoesNotCoverIntOrString() */
    #[Catch_(ValueError::class, from: DeliveryMethod::class, match: enum_value)] // an already hydrated enum
    private DeliveryMethod $reshipmentMethod;

    /** @see EnumValueMatchConditionCompilerUnitTest::testThrowsWhenFromClassIsNotBackedEnum() */
    #[Catch_(ValueError::class, from: [DeliveryIssue::class, 'from'], match: enum_value)]
    private mixed $issue;

    /** @see EnumValueMatchConditionCompilerUnitTest::testThrowsWhenFromMethodIsNotEnumFrom() */
    #[Catch_(ValueError::class, from: [DeliveryMethod::class, 'tryFrom'], match: enum_value)]
    private mixed $deliveryMethod;

    /** @see EnumValueMatchConditionCompilerUnitTest::testThrowsWhenMatchConditionUsesNonValueErrorExceptionClass() */
    #[Catch_(RuntimeException::class, from: DeliveryMethod::class, match: enum_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    private mixed $returnMethod;

    /** @see EnumValueMatchConditionCompilerUnitTest::testThrowsWhenFromClauseIsMissing() */
    #[Catch_(ValueError::class, match: enum_value)]
    private mixed $deliveryPriority;
}
