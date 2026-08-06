<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\_Plan\_Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\_Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMappingPlanCompiler<ReflectionClass<object>,ObjectExceptionMappingPlan<object>>
 */
final class ObjectExceptionMappingPlanCompiler implements ExceptionMappingPlanCompiler
{
    public function __construct(
        /** @var ExceptionMappingPlanCompiler<ReflectionProperty,PropertyExceptionMappingPlan> */
        private readonly ExceptionMappingPlanCompiler $propertyMappingPlanCompiler,
        private readonly bool $throwOnFailure = true,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /** @param ReflectionClass<object> $reflector */
    public function compilePlan(Reflector $reflector): ?ObjectExceptionMappingPlan
    {
        try {
            return $this->compile($reflector);
        } catch (Throwable $exception) {
            $e = new ObjectExceptionMappingPlanCompilationFailedException($reflector->getName(), $exception);

            if (!$this->throwOnFailure) {
                // One broken class mapping won't spoil the whole situation.
                $this->logger?->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }

    private function compile(ReflectionClass $reflectionClass): ?ObjectExceptionMappingPlan
    {
        if ([] === $reflectionClass->getAttributes(Try_::class)) {
            return null;
        }

        $mappingPlan = new ObjectExceptionMappingPlan(
            $reflectionClass->getName(),
            new ReusableIteratorAggregate($this->compilePropertyPlans($reflectionClass)),
        );

        if (!$mappingPlan->hasPropertyPlans()) {
            return null;
        }

        return $mappingPlan;
    }

    /** @return Generator<int,PropertyExceptionMappingPlan> */
    private function compilePropertyPlans(ReflectionClass $reflectionClass): Generator
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyPlan = $this->propertyMappingPlanCompiler->compilePlan($reflectionProperty);

            if (null === $propertyPlan) {
                continue;
            }

            yield $propertyPlan;
        }
    }
}
