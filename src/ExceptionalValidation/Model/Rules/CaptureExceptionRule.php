<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rules;

use PhPhD\ExceptionalValidation\Model\CaptureRule;
use PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use Throwable;

final class CaptureExceptionRule implements CaptureRule
{
    /** @param class-string<Throwable> $exceptionClass */
    public function __construct(
        private readonly CaptureRule $parent,
        private readonly string $exceptionClass,
        private readonly string $message,
    ) {
    }

    public function capture(Throwable $exception): ?CaughtException
    {
        if (!$exception instanceof $this->exceptionClass) {
            return null;
        }

        return new CaughtException($exception, $this);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
    }

    public function getRoot(): object
    {
        return $this->parent->getRoot();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getValue(): mixed
    {
        return $this->parent->getValue();
    }

    /** @return class-string<Throwable> */
    public function getExceptionClass(): string
    {
        return $this->exceptionClass;
    }
}
