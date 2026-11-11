<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch;
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
     * @param ExceptionMatch<TException&Throwable> $exceptionMatch
     *
     * @return non-empty-list<TResult>
     */
    public function format(ExceptionMatch $exceptionMatch): array;
}
