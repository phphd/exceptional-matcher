<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use Throwable;

/**
 * @internal
 *
 * @template T of Throwable
 */
interface MatchCondition
{
    /** @param T $exception */
    public function matches(Throwable $exception): bool;
}
