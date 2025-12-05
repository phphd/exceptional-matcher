<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item;

use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
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
interface PropriatedExceptionFormatter
{
    /**
     * @param PropriatedException<TException> $propriatedException
     *
     * @return non-empty-list<TResult>
     */
    public function format(PropriatedException $propriatedException): array;
}
