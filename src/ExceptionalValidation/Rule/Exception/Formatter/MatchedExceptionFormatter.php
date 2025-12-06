<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception\Formatter;

use PhPhD\ExceptionalValidation\Rule\Exception\MatchedException;
use Throwable;

/**
 * @api
 *
 * @phpstan-template-contravariant TException of Throwable
 *
 * @psalm-template-covariant TException of Throwable (psalm doesn't support contravariant templates)
 *
 * @template-covariant TResult of mixed
 *
 * @psalm-immutable
 */
interface MatchedExceptionFormatter
{
    /**
     * @param MatchedException<TException> $matchedException
     *
     * @return non-empty-list<TResult>
     */
    public function format(MatchedException $matchedException): array;
}
