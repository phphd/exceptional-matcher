<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub;

use DivisionByZeroError;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedException;

/** @implements MatchedExceptionFormatter<DivisionByZeroError,array{}> */
final class UnregisteredFormatter implements MatchedExceptionFormatter
{
    /** @return non-empty-list<array{}> */
    public function format(MatchedException $matchedException): array
    {
        return [[]];
    }
}
