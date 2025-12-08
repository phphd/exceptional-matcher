<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use Throwable;

/**
 * @internal
 *
 * @phpstan-template T of Throwable
 *
 * @psalm-template T of mixed (psalm doesn't support contravariant templates)
 */
interface MatchCondition
{
    /** @param T $exception */
    public function matches(Throwable $exception): bool;
}
