<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
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
