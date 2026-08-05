<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use Closure;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use ReflectionClass;

final class CompilingObjectExceptionMappingPlanRegistry implements ObjectExceptionMappingPlanRegistry
{
    public function __construct(
        private readonly ObjectExceptionMappingPlanCompiler $planCompiler,
    ) {
    }

    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    public function getPlan(string $className): ?ObjectExceptionMappingPlan
    {
        return $this->planCompiler->compilePlan(new ReflectionClass($className), $this);
    }
}
