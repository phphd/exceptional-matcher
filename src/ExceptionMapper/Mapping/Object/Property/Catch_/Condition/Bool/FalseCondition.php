<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Bool;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 *
 * @implements MatchCondition<T>
 */
final class FalseCondition implements MatchCondition
{
    public function matches(Throwable $exception): bool
    {
        return false;
    }
}
