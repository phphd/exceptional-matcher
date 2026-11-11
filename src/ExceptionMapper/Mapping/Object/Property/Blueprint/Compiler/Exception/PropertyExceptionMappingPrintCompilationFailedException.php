<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception;

use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use ReflectionProperty;
use Throwable;

/** @internal */
final class PropertyExceptionMappingPrintCompilationFailedException extends ExceptionMappingPrintCompilationFailedException
{
    public function __construct(
        private readonly ReflectionProperty $property,
        Throwable $previous,
    ) {
        parent::__construct(
            'Property exception mapping compilation failed.',
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
