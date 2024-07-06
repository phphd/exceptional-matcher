<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception\Adapter;

use RuntimeException;
use Throwable;

/** @api */
final class SingleThrownException extends RuntimeException implements ThrownException
{
    public function __construct(
        private readonly Throwable $exception,
    ) {
        parent::__construct(previous: $this->exception);
    }

    public function getExceptions(): array
    {
        return [$this->exception];
    }

    public function getOriginalException(): Throwable
    {
        return $this->exception;
    }
}
