<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Bool;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

/**
 * @internal
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
