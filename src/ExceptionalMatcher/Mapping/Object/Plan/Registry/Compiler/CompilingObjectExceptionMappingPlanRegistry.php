<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 *
 * @template T of object
 *
 * @implements ObjectExceptionMappingPlanRegistry<T>
 */
final class CompilingObjectExceptionMappingPlanRegistry implements ObjectExceptionMappingPlanRegistry
{
    public function __construct(
        /** @var ExceptionMappingPlanCompiler<ReflectionClass<T>,ObjectExceptionMappingPlan<T>> */
        private readonly ExceptionMappingPlanCompiler $planCompiler,
    ) {
    }

    /**
     * @param class-string<T> $className
     *
     * @throws ReflectionException
     */
    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    /**
     * @param class-string<T> $className
     *
     * @return null|ObjectExceptionMappingPlan<T>
     *
     * @throws ReflectionException
     */
    public function getPlan(string $className): ?ObjectExceptionMappingPlan
    {
        return $this->planCompiler->compilePlan(new ReflectionClass($className));
    }
}
