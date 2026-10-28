<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingCompiler;
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
    /** @api */
    public function __construct(
        /** @var ExceptionMappingCompiler<ReflectionClass<T>,ObjectExceptionMappingPlan<T>> */
        private readonly ExceptionMappingCompiler $planCompiler,
    ) {
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
