<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\_Exception;

use ReflectionProperty;
use RuntimeException;
use Throwable;

final class PropertyExceptionMappingPlanCompilationFailedException  extends RuntimeException
{
    public function __construct(
        private readonly ReflectionProperty $reflectionProperty,
        Throwable $previous,
    ) {
        parent::__construct(sprintf(), previous: $previous);
    }

    public function getReflectionProperty(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }
}
