<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Compiler;

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
    public function create(string $className, ObjectExceptionMappingPlanRegistry $planRegistry): ?ObjectExceptionMappingPlan
    {
        $reflectionClass = new ReflectionClass($className);

        if ([] === $reflectionClass->getAttributes(Try_::class)) {
            return null;
        }

        $classMappingPlan = new ObjectExceptionMappingPlan(
            $className,
            new ReusableIteratorAggregate($this->compilePropertyPlans($reflectionClass, $planRegistry)),
        );

        if (!$classMappingPlan->hasPropertyPlans()) {
            return null;
        }

        return $classMappingPlan;
    }

    /** @return Generator<int,PropertyExceptionMappingPlan> */
    private function compilePropertyPlans(ReflectionClass $reflectionClass, ObjectExceptionMappingPlanRegistry $planRegistry): Generator
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyPlan = $this->propertyMappingPlanCompiler->getPropertyPlan($reflectionProperty, $planRegistry);

            if (null === $propertyPlan) {
                continue;
            }

            yield $propertyPlan;
        }
    }
}
