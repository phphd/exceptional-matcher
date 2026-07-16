<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Exception\MatchedException;

/** @implements MatchedExceptionFormatter<LinterStubException,array{}> */
final class UnregisteredFormatter implements MatchedExceptionFormatter
{
    /** @return non-empty-list<array{}> */
    public function format(MatchedException $matchedException): array
    {
        return [[]];
    }
}
