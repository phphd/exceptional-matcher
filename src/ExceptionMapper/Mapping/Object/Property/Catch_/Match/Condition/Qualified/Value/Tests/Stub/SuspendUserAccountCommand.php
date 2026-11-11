<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests\ExceptionValueConditionRule;
use PhPhD\ExceptionMapper\Tests\Probe\ProbeOf;
use RuntimeException;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\exception_value;

final class SuspendUserAccountCommand
{
    #[ProbeOf(ExceptionValueConditionRule::NonValueExceptionClassThrows)]
    #[Catch_(RuntimeException::class, match: exception_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    private mixed $reason;
}
