<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use Throwable;

/**
 * @internal
 *
 * @phpstan-template-contravariant T of Throwable
 *
 * @psalm-template-covariant T of Throwable (psalm doesn't support contravariant templates)
 *
 * @psalm-immutable
 */
interface MatchCondition
{
    /** @param T $exception */
    public function matches(Throwable $exception): bool;
}
