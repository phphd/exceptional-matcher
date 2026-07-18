<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use Generator;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyPlan;
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
    private const MATCHABLE_BUILTIN_TYPES = ['array', 'iterable', 'mixed', 'object'];

    public function __construct(
        /** @var MatchConditionCompiler<Throwable> */
        private readonly MatchConditionCompiler $matchConditionCompiler,
    ) {
    }

    /** @param ReflectionClass<object> $reflectionClass */
    public function create(ReflectionClass $reflectionClass, ClassMatchingPlanRegistry $planRegistry): ClassMatchingPlan
    {
        return new ClassMatchingPlan(
            new RestartableIteratorAggregate(fn (): Generator => $this->createPropertyPlans($reflectionClass, $planRegistry)),
        );
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<int,PropertyPlan>
     */
    private function createPropertyPlans(ReflectionClass $reflectionClass, ClassMatchingPlanRegistry $planRegistry): Generator
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $catchPlans = new RestartableIteratorAggregate(fn (): Generator => $this->createCatchPlans($reflectionProperty));

            if (!$this->isMatchableProperty($catchPlans, $reflectionProperty, $planRegistry)) {
                continue;
            }

            yield new PropertyPlan($reflectionProperty, $catchPlans, $planRegistry);
        }
    }

    /** @return Generator<int,CatchPlan<Throwable>> */
    private function createCatchPlans(ReflectionProperty $property): Generator
    {
        foreach ($property->getAttributes(Catch_::class) as $attribute) {
            $catch = $attribute->newInstance();

            $conditionBlueprint = $this->matchConditionCompiler->compile($catch);

            Assert::notNull($conditionBlueprint);

            yield new CatchPlan($conditionBlueprint, $catch->getFormat(), $catch->getMessage());
        }
    }

    /**
     * A property participates in matching when it declares any `#[Catch_]`, or when its value may
     * turn out to be a nested `#[Try_]` object or an iterable holding such objects.
     *
     * @param RestartableIteratorAggregate<int,CatchPlan<Throwable>> $catchPlans
     */
    private function isMatchableProperty(
        RestartableIteratorAggregate $catchPlans,
        ReflectionProperty $property,
        ClassMatchingPlanRegistry $planRegistry,
    ): bool {
        try {
            if ($catchPlans->getIterator()->valid()) {
                return true;
            }
        } catch (Throwable) {
            // a broken catch mapping keeps the property matchable:
            // the failure resurfaces once the catch plans are accessed
            return true;
        }

        return $this->canValueMatch($property->getType(), $planRegistry);
    }

    private function canValueMatch(?ReflectionType $type, ClassMatchingPlanRegistry $planRegistry): bool
    {
        if ($type instanceof ReflectionNamedType) {
            return $this->canNamedTypeValueMatch($type, $planRegistry);
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            foreach ($type->getTypes() as $reflectionType) {
                if ($this->canValueMatch($reflectionType, $planRegistry)) {
                    return true;
                }
            }

            return false;
        }

        // untyped property - the value may be anything
        return true;
    }

    private function canNamedTypeValueMatch(ReflectionNamedType $type, ClassMatchingPlanRegistry $planRegistry): bool
    {
        if ($type->isBuiltin()) {
            return in_array($type->getName(), self::MATCHABLE_BUILTIN_TYPES, true);
        }

        $className = $type->getName();

        if (!class_exists($className) && !interface_exists($className)) {
            // unresolvable type reference (e.g. relative `self`) - keep the property to stay on the safe side
            return true;
        }

        if (is_a($className, Traversable::class, true)) {
            return true;
        }

        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isInternal() || $reflectionClass->isEnum()) {
            // built-in classes / interface implementations cannot declare #[Try_]
            return false;
        }

        if ($reflectionClass->isInterface()) {
            // any implementor - including one bearing #[Try_] - may be assigned
            return true;
        }

        if (!$reflectionClass->isFinal()) {
            // #[Try_] is not inherited, yet a plan-bearing subclass may still be assigned at runtime
            return true;
        }

        return $planRegistry->hasPlan($className);
    }
}
