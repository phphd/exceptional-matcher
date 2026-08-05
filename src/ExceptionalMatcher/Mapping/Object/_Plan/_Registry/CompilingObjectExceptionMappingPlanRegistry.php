<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use Closure;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use ReflectionClass;

final class CompilingObjectExceptionMappingPlanRegistry implements ObjectExceptionMappingPlanRegistry
{
    /** @var array<class-string,?ObjectExceptionMappingPlan> */
    private array $plans = [];

    public function __construct(
        private readonly ObjectExceptionMappingPlanCompiler $planCompiler,
        private ?Closure $autoloadClassNames,
    ) {
    }

    /** @param class-string $className */
    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    /** @param class-string $className */
    public function getPlan(string $className): ?ObjectExceptionMappingPlan
    {
        if (null !== $this->autoloadClassNames) {
            $this->autoloadClassNames->__invoke();
            $this->autoloadClassNames = null;
        }

        return $this->planCompiler->compilePlan(new ReflectionClass($className), $this);
    }
}
