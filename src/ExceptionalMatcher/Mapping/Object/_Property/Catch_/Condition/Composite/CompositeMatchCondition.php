<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Composite;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final class CompositeMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var iterable<MatchCondition<Throwable>> */
        private readonly iterable $conditions,
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
