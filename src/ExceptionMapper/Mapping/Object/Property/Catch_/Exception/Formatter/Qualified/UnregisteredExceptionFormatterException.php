<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified;

use LogicException;

use function sprintf;

final class UnregisteredExceptionFormatterException extends LogicException
{
    /** @param class-string $formatterId */
    public function __construct(string $formatterId)
    {
        parent::__construct(sprintf('Exception Formatter is not registered: "%s"', $formatterId));
    }
}
