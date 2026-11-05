<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests\ExceptionValueConditionRule;
use PhPhD\ExceptionalMatcher\Tests\Probe\ProbeOf;
use RuntimeException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\exception_value;

final class SuspendUserAccountCommand
{
    #[ProbeOf(ExceptionValueConditionRule::NonValueExceptionClassThrows)]
    #[Catch_(RuntimeException::class, match: exception_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    private mixed $reason;
}
