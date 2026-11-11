<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub;

use DivisionByZeroError;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;

/** @implements MatchedExceptionFormatter<DivisionByZeroError,array{}> */
final class UnregisteredFormatter implements MatchedExceptionFormatter
{
    /** @return non-empty-list<array{}> */
    public function format(ExceptionMatch $exceptionMatch): array
    {
        return [[]];
    }
}
