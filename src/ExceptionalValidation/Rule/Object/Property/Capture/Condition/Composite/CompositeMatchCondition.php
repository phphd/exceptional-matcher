<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final class CompositeMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var list<MatchCondition<Throwable>> */
        private readonly array $conditions,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        foreach ($this->conditions as $condition) {
            if (!$condition->matches($exception)) {
                return false;
            }
        }

        return true;
    }
}
