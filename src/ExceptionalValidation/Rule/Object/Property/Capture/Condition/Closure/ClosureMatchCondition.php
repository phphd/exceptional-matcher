<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final class ClosureMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var Closure(Throwable): bool */
        private readonly Closure $condition,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        return ($this->condition)($exception);
    }
}
