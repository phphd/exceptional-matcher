<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use ReflectionClass;

/** @internal */
final class ObjectExceptionMappingPlanCompiler
{
    public function __construct(
        private readonly PropertyExceptionMappingPlanCompiler $propertyMappingPlanCompiler,
    ) {
    }

    /** @param class-string $className */
    public function compilePlan(string $className, ObjectExceptionMappingPlanRegistry $planRegistry): ?ObjectExceptionMappingPlan
    {
        return $this->compile($className, $planRegistry);
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
