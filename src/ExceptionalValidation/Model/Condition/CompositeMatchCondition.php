<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Condition;

use Throwable;

use function count;

/** @internal */
final class CompositeMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var list<MatchCondition> */
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

    public function compile(): MatchCondition
    {
        if (count($this->conditions) === 1) {
            return $this->conditions[0];
        }

        return $this;
    }
}
