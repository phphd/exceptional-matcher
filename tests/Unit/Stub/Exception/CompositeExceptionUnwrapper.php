<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception;

use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Throwable;

use function array_map;
use function array_merge;

/** @internal */
final class CompositeExceptionUnwrapper implements ExceptionUnwrapper
{
    public function __construct(
        private readonly ExceptionUnwrapper $innerUnwrapper,
    ) {
    }

    public function unwrap(Throwable $exception): array
    {
        if (!$exception instanceof CompositeException) {
            return $this->innerUnwrapper->unwrap($exception);
        }

        $unwrapped = array_map(
            $this->innerUnwrapper->unwrap(...),
            $exception->getExceptions(),
        );

        return array_merge(...$unwrapped);
    }
}
