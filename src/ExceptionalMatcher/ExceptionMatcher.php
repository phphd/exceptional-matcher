<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use Throwable;

/**
 * Exception Matcher returns matched exception objects only if all exceptions were correctly matched.
 * If at least one exception has not been matched to any of the properties, null is returned.
 *
 * @api
 *
 * @template-covariant T of mixed
 */
interface ExceptionMatcher
{
    /** @return ?T */
    public function match(Throwable $exception, object $message): mixed;
}
