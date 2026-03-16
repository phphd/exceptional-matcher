<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition;

use Throwable;

/**
 * @internal - this might be exposed as an api in some future versions
 *
 * @phpstan-template-contravariant T of Throwable
 *
 * @psalm-template T of mixed (psalm doesn't support contravariant templates)
 */
interface MatchCondition
{
    /** @param T $exception */
    public function matches(Throwable $exception): bool;
}
