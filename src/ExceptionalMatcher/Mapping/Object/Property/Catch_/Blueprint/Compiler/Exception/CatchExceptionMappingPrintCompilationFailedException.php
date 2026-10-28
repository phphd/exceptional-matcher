<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use Throwable;

/** @internal */
final class CatchExceptionMappingPrintCompilationFailedException extends ExceptionMappingPrintCompilationFailedException
{
    public function __construct(
        /** @var class-string */
        private readonly string $className,
        private readonly string $propertyName,
        Throwable $previous,
    ) {
        parent::__construct(
            'CatchExceptionMappingBlueprint compilation failed.',
            previous: $previous,
        );
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
