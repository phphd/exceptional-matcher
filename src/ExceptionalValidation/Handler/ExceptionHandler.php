<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use Throwable;

interface ExceptionHandler
{
    /** @throws Throwable */
    public function capture(object $message, Throwable $exception): never;
}
