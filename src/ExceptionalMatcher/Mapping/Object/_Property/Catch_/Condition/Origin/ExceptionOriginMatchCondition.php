<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Origin;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final class ExceptionOriginMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var ?class-string */
        private readonly ?string $originClassName = null,
        /** @var ?non-empty-string */
        private readonly ?string $originFunctionName = null,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        foreach ($exception->getTrace() as $traceItem) {
            if (isset($this->originClassName) && $this->originClassName !== ($traceItem['class'] ?? null)) {
                continue;
            }

            if (isset($this->originFunctionName) && $this->originFunctionName !== ($traceItem['function'] ?? null)) { // @phpstan-ignore nullCoalesce.offset
                continue;
            }

            return true;
        }

        return false;
    }
}
