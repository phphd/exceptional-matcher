<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\ValueObject;

use Throwable;

final class ThrownException
{
    public function __construct(
        private readonly Throwable $exception,
    ) {
    }

    /** @param class-string<Throwable> $exceptionClass */
    public function match(string $exceptionClass): ?Throwable
    {
        if (!$this->exception instanceof $exceptionClass) {
            return null;
        }

        return $this->exception;
    }
}
