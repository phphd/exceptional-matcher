<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Exception\Formatter;

use PhPhD\ExceptionalMatcher\Rule\Exception\MatchedException;
use Throwable;

/**
 * @api
 *
 * @phpstan-template-contravariant TException of Throwable
 *
 * @psalm-template TException of mixed (psalm doesn't support contravariant templates)
 *
 * @template-covariant TResult of mixed
 */
interface MatchedExceptionFormatter
{
    /**
     * @param MatchedException<TException&Throwable> $matchedException
     *
     * @return non-empty-list<TResult>
     */
    public function format(MatchedException $matchedException): array;
}
