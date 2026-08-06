<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use ReflectionClass;

final class CompilingObjectExceptionMappingPlanRegistry implements ObjectExceptionMappingPlanRegistry
{
    public function __construct(
        /** @var ExceptionMappingPlanCompiler<ReflectionClass<object>,ObjectExceptionMappingPlan<object>> */
        private readonly ExceptionMappingPlanCompiler $planCompiler,
    ) {
    }

    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    public function getPlan(string $className): ?ObjectExceptionMappingPlan
    {
        return $this->planCompiler->compilePlan(new ReflectionClass($className));
    }
}
