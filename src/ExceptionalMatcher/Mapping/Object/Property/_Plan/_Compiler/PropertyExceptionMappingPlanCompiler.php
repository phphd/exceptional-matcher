<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\CatchExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use ReflectionProperty;
use Throwable;

/** @internal */
final class PropertyExceptionMappingPlanCompiler
{
    public function __construct(
        private readonly CatchExceptionMappingPlanCompiler $catchPlanCompiler,
    ) {
    }

    public function compilePlan(
        ReflectionProperty $reflectionProperty,
        ObjectExceptionMappingPlanRegistry $planRegistry
    ): ?PropertyExceptionMappingPlan {
        return $this->compile($reflectionProperty, $planRegistry);
    }

    private function compile(ReflectionProperty $reflectionProperty, ObjectExceptionMappingPlanRegistry $planRegistry): ?PropertyExceptionMappingPlan
    {
        $catchPlans = new ReusableIteratorAggregate($this->compileCatchPlans($reflectionProperty));

        $propertyMappingPlan = new PropertyExceptionMappingPlan($reflectionProperty, $catchPlans, $planRegistry);

        if (!$propertyMappingPlan->hasCatchPlans()) {
            $type = new PropertyTypeAnalyser($reflectionProperty->getType());

            if (!$type->allowsMatchableObjects($planRegistry)) {
                return null;
            }
        }

        return $propertyMappingPlan;
    }

    /** @return Generator<int,CatchExceptionMappingPlan<Throwable>> */
    private function compileCatchPlans(ReflectionProperty $property): Generator
    {
        foreach ($property->getAttributes(Catch_::class) as $catchAttribute) {
            $catchPlan = $this->catchPlanCompiler->compilePlan($catchAttribute);

            if (null === $catchPlan) {
                continue;
            }

            yield $catchPlan;
        }
    }
}
