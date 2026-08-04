<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception;

use RuntimeException;

use function sprintf;

final class UnregisteredFormatterException extends RuntimeException
{
    public function __construct(
        /** @var class-string */
        private readonly string $formatterId,
    ) {
        parent::__construct(sprintf('Formatter "%s" is not registered in the formatter registry.', $formatterId));
    }

    /** @return class-string */
    public function getFormatterId(): string
    {
        return $this->formatterId;
    }
}
