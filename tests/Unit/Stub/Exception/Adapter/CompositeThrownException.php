<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub\Exception\Adapter;

use PhPhD\ExceptionalValidation\Model\Exception\Adapter\ThrownException;
use RuntimeException;
use Throwable;

/** @internal */
final class CompositeThrownException extends RuntimeException implements ThrownException
{
    public function __construct(
        /** @var list<Throwable> */
        private readonly array $exceptions,
    ) {
        parent::__construct();
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function getOriginalException(): Throwable
    {
        return $this;
    }
}
