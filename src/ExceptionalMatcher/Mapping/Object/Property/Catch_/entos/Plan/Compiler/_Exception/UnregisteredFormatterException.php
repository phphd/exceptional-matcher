<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\entos\Plan\Compiler\_Exception;

use RuntimeException;

use function sprintf;

final class UnregisteredFormatterException extends RuntimeException
{
    /** @param class-string $formatterId */
    public function __construct(string $formatterId)
    {
        parent::__construct(sprintf('Formatter "%s" is not registered in the formatter registry.', $formatterId));
    }
}
