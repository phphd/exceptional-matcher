<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use Throwable;

use function sprintf;

/** @internal */
final class ObjectExceptionMappingPrintCompilationFailedException extends ExceptionMappingPrintCompilationFailedException
{
    /** @param class-string $className */
    public function __construct(
        private readonly string $className,
        Throwable $previous,
    ) {
        parent::__construct(
            sprintf('Class %s exception mapping compilation has failed.', $className),
            previous: $previous,
        );
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->className;
    }
}
