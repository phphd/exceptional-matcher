<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Compiler;

use AppendIterator;
use Exception;
use Generator;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Plan\ClassMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Throwable;
use Traversable;
use Webmozart\Assert\Assert;

use function class_exists;
use function in_array;
use function interface_exists;
use function is_a;

/** @internal */
final class ClassMatchingPlanFactory
{
    public function __construct(
        private readonly PropertyMappingPlanCompiler $propertyMappingPlanCompiler,
    ) {
    }

    /** @param class-string $className */
    public function create(string $className, ClassMatchingPlanRegistry $planRegistry): ?ClassMappingPlan
    {
        $reflectionClass = new ReflectionClass($className);

        if ([] === $reflectionClass->getAttributes(Try_::class)) {
            return null;
        }

        $classMappingPlan = new ClassMappingPlan(
            $reflectionClass,
            new ReusableIteratorAggregate($this->compilePropertyPlans($reflectionClass, $planRegistry)),
        );

        if (!$classMappingPlan->hasPropertyPlans()) {
            return null;
        }

        return $classMappingPlan;
    }

    /** @return Generator<int,PropertyMappingPlan> */
    private function compilePropertyPlans(ReflectionClass $reflectionClass, ClassMatchingPlanRegistry $planRegistry): Generator
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
