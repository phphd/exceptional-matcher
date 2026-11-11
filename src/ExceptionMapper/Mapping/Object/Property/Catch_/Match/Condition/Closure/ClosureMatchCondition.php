<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Closure;

use Closure;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\MatchCondition;
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
