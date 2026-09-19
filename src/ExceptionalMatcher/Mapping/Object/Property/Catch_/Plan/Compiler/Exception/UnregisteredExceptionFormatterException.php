<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception;

use RuntimeException;

use function sprintf;

final class UnregisteredExceptionFormatterException extends RuntimeException
{
    /** @param class-string $formatterId */
    public function __construct(string $formatterId)
    {
        parent::__construct(sprintf('Exception Formatter "%s" is not registered in the formatter registry.', $formatterId));
    }
}
