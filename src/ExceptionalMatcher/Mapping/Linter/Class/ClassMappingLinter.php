<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use ReflectionClass;
use ReflectionProperty;

use function iterator_to_array;
use function sprintf;

/**
 * @internal
 *
 * @implements MappingLinter<class-string,LintReport>
 */
final class ClassMappingLinter implements MappingLinter
{
    public function __construct(
        /** @var ObjectExceptionMappingPlanRegistry<object> */
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
        /** @var ExceptionMappingPlanCompiler<ReflectionProperty,PropertyExceptionMappingPlan> */
        private readonly ExceptionMappingPlanCompiler $propertyMappingPlanCompiler,
        private readonly MappingDefectCollector $defectCollector,
    ) {
    }

    /** @param iterable<class-string> $symbols */
    public function lint(iterable $symbols): LintReport
    {
        $processed = 0;

        /** @var list<iterable<MappingDefect>> $defects */
        $defects = [];

        foreach ($symbols as $className) {
            $defects[] = $this->lintClass(new ReflectionClass($className));

            ++$processed;
        }

        return new LintReport($processed, array_merge(...array_map(iterator_to_array(...), $defects)));
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<MappingDefect>
     */
    private function lintClass(ReflectionClass $reflectionClass): Generator
    {
        if ($reflectionClass->isEnum()) {
            return;
        }

        $compilationDefects = $this->lintPlan($reflectionClass);

        yield from $compilationDefects;

        $plan = $this->planRegistry->getPlan($reflectionClass->getName());

        yield from $this->lintStructure($reflectionClass, $plan, [] !== $compilationDefects);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return array<MappingDefect>
     */
    private function lintPlan(ReflectionClass $reflectionClass): array
    {
        $plan = $this->planRegistry->getPlan($reflectionClass->getName());

        // Compilation is done lazily through traversal
        foreach ($plan?->getPropertyPlans() ?? [] as $propertyPlan) {
            foreach ($propertyPlan->getCatchPlans() as $catchPlan) {
                unset($catchPlan);
            }
            unset($propertyPlan);
        }

        $compilationDefects = $this->defectCollector->flush();

        if ([] !== $compilationDefects || null !== $plan) {
            return $compilationDefects;
        }

        // The class plan is null, and no compilation errors were reported.
        $missingTryDefects = [];

        foreach ($reflectionClass->getProperties() as $property) {
            // if class's property has a catch plan, and class has no plan, - that's a defect
            if (true !== $this->propertyMappingPlanCompiler->compilePlan($property)?->hasCatchPlans()) {
                continue;
            }

            $missingTryDefects [] = MappingDefect::warning(
                'Properties declare #[Catch_] mappings, but the class is not marked with #[Try_], so it never matches anything.',
                new DefectLocation($reflectionClass->getName(), $property->getName()),
            );
        }

        return $missingTryDefects;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param ?ObjectExceptionMappingPlan<object> $plan
     *
     * @return Generator<MappingDefect>
     */
    private function lintStructure(ReflectionClass $reflectionClass, ?ObjectExceptionMappingPlan $plan, bool $compilationFailed): Generator
    {
        $classLocation = new DefectLocation($reflectionClass->getName());

        if (!$this->hasTryAttribute($reflectionClass)) {
            return;
        }

        if ($reflectionClass->isAbstract()) {
            yield MappingDefect::warning(
                '#[Try_] on an abstract class never matches: attributes are not inherited by its subclasses.',
                $classLocation,
            );
        } elseif (null === $plan && !$compilationFailed) {
            // a plan missing because its mappings failed to compile is already reported as an error
            yield MappingDefect::warning(
                '#[Try_] class declares no #[Catch_] mappings and no nested matchable properties, so it never matches anything.',
                $classLocation,
            );
        }

        yield from $this->lintParentPrivateCatches($reflectionClass);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<MappingDefect>
     */
    private function lintParentPrivateCatches(ReflectionClass $reflectionClass): Generator
    {
        for ($parent = $reflectionClass->getParentClass(); false !== $parent; $parent = $parent->getParentClass()) {
            foreach ($parent->getProperties(ReflectionProperty::IS_PRIVATE) as $parentProperty) {
                if (!$this->hasCatchAttributes($parentProperty)) {
                    continue;
                }

                yield MappingDefect::warning(
                    sprintf(
                        'Private property %s::$%s declares #[Catch_] mappings that are invisible to %s.',
                        $parent->getName(),
                        $parentProperty->getName(),
                        $reflectionClass->getName(),
                    ),
                    new DefectLocation($reflectionClass->getName(), $parentProperty->getName()),
                );
            }
        }
    }

    /** @param ReflectionClass<object> $reflectionClass */
    private function hasTryAttribute(ReflectionClass $reflectionClass): bool
    {
        return [] !== $reflectionClass->getAttributes(Try_::class);
    }

    private function hasCatchAttributes(ReflectionProperty $reflectionProperty): bool
    {
        return [] !== $reflectionProperty->getAttributes(Catch_::class);
    }
}
