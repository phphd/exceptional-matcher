<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\_Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Throwable;

/** @internal */
final class ObjectExceptionMappingPlanCompiler
{
    public function __construct(
        private readonly PropertyExceptionMappingPlanCompiler $propertyMappingPlanCompiler,
        private readonly bool $throwOnFailure = true,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /** @param class-string $className */
    public function compilePlan(string $className, ObjectExceptionMappingPlanRegistry $planRegistry): ?ObjectExceptionMappingPlan
    {
        try {
            return $this->compile($className, $planRegistry);
        } catch (Throwable $exception) {
            $e = new ObjectExceptionMappingPlanCompilationFailedException($className, $exception);

            if (!$this->throwOnFailure) {
                // One broken class mapping won't spoil the whole situation.
                $this->logger?->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }

    private function compile(string $className, ObjectExceptionMappingPlanRegistry $planRegistry): ?ObjectExceptionMappingPlan
    {
        $reflectionClass = new ReflectionClass($className);

        if ([] === $reflectionClass->getAttributes(Try_::class)) {
            return null;
        }

        $mappingPlan = new ObjectExceptionMappingPlan(
            $className,
            new ReusableIteratorAggregate($this->compilePropertyPlans($reflectionClass, $planRegistry)),
        );

        if (!$mappingPlan->hasPropertyPlans()) {
            return null;
        }

        return $mappingPlan;
    }

    /** @return Generator<int,PropertyExceptionMappingPlan> */
    private function compilePropertyPlans(ReflectionClass $reflectionClass, ObjectExceptionMappingPlanRegistry $planRegistry): Generator
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyPlan = $this->propertyMappingPlanCompiler->compilePlan($reflectionProperty, $planRegistry);

            if (null === $propertyPlan) {
                continue;
            }

            yield $propertyPlan;
        }
    }
}
