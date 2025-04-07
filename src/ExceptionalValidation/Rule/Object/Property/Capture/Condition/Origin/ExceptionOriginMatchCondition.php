<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

/** @internal */
final class ExceptionOriginMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly string $originClassName,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        foreach ($exception->getTrace() as $traceItem) {
            $class = $traceItem['class'] ?? null;

            if ($class === $this->originClassName) {
                return true;
            }
        }

        return false;
    }
}
