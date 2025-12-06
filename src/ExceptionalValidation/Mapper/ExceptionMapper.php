<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper;

use Throwable;

/**
 * Exception Mapper returns mapped objects only if all exceptions were correctly mapped to their respective properties.
 * If at least one exception has not been mapped, then null is returned.
 *
 * @api
 *
 * @template-covariant T of mixed
 */
interface ExceptionMapper
{
    /** @return ?T */
    public function map(object $message, Throwable $exception): mixed;
}
