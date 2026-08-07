<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Class;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final class ExceptionClassMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var class-string<Throwable> */
        private readonly string $exceptionClass,
    ) {
    }

    public function matches(Throwable $exception): bool
    {
        return $exception instanceof $this->exceptionClass;
    }
}
