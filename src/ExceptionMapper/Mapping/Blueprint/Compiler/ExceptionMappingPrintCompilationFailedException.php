<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler;

use RuntimeException;
use Throwable;

/** @internal */
abstract class ExceptionMappingPrintCompilationFailedException extends RuntimeException
{
    public function getRootException(): Throwable
    {
        $root = $this;

        while (null !== $previous = $root->getPrevious()) {
            $root = $previous;
        }

        return $root;
    }

    /** @return class-string */
    abstract public function getClassName(): string;

    abstract public function getPropertyName(): ?string;
}
