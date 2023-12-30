<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation;

use Attribute;
use Exception;
use Throwable;

/** @api */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Capture
{
    public function __construct(
        /** @var class-string<Exception> */
        private readonly string $exception,
        private readonly string $message,
    ) {
    }

    /** @return class-string<Throwable> */
    public function getExceptionClass(): string
    {
        return $this->exception;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
