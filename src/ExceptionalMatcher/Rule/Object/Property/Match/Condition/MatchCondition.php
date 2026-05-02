<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition;

use Throwable;

/**
 * @api
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
