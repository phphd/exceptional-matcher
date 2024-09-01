<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception;

use RuntimeException;
use Throwable;

/** @internal */
final class CompositeException extends RuntimeException
{
    public function __construct(
        /** @var non-empty-list<Throwable> */
        private readonly array $exceptions,
    ) {
        parent::__construct();
    }

    /** @return non-empty-list<Throwable> */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
