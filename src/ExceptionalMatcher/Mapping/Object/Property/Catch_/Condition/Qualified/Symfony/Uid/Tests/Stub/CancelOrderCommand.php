<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\Tests\UidValueMatchConditionCompilerUnitTest;
use RuntimeException;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\uid_value;

final class CancelOrderCommand
{
    /** @see UidValueMatchConditionCompilerUnitTest::testCompilesWhenPropertyTypeCoversString() */
    #[Catch_(InvalidUidException::class, match: uid_value)] // a legacy auto-increment id or UUID
    private int|string $orderId;

    /** @see UidValueMatchConditionCompilerUnitTest::testThrowsWhenPropertyTypeDoesNotCoverString() */
    #[Catch_(InvalidUidException::class, match: uid_value)]
    private int $legacyOrderId;

    /** @see UidValueMatchConditionCompilerUnitTest::testThrowsWhenMatchConditionUsesNonInvalidUidExceptionClass() */
    #[Catch_(RuntimeException::class, match: uid_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    private mixed $customerId;
}
