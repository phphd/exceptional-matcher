<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\ExceptionClass;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

/** @internal */
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
