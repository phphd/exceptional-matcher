<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Compiler;

use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use ReflectionProperty;

final class PropertyMappingPlanCompiler
{
    public function __construct(
        private ClassMatchingPlanRegistry $planRegistry
    ) {
    }

    public function compile(ReflectionProperty $reflectionProperty)
    {
        
    }
}
