<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler;

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
}
