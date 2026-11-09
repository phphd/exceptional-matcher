<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use ReflectionProperty;
use Throwable;

/** @internal */
final class CatchExceptionMappingPrintCompilationFailedException extends ExceptionMappingPrintCompilationFailedException
{
    public function __construct(
        private readonly ReflectionProperty $property,
        Throwable $previous,
    ) {
        parent::__construct(
            '#[Catch_] exception mapping compilation failed.',
            previous: $previous,
        );
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->property->getDeclaringClass()
            ->getName()
        ;
    }

    public function getPropertyName(): string
    {
        return $this->property->getName();
    }
}
