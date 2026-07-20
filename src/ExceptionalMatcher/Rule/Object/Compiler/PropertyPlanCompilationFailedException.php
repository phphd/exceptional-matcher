<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Compiler;

use ReflectionProperty;
use RuntimeException;
use Throwable;

final class PropertyPlanCompilationFailedException extends RuntimeException
{
    public function __construct(
        private readonly ReflectionProperty $property,
        Throwable $previous,
    ) {
        parent::__construct('Property plan compilation has failed', previous: $previous);
    }

    public function getProperty(): ReflectionProperty
    {
        return $this->property;
    }
}
