<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Defect\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Defect\MappingDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

/**
 * @internal
 *
 * @implements MappingLinter<class-string,LintReport>
 */
final class ClassNameBasedLinter implements MappingLinter
{
    public function __construct(
        /** @var ObjectExceptionMappingPlanRegistry<object> */
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
        private readonly MappingDefectCollector $defectCollector,
    ) {
    }

    /** @param iterable<class-string> $symbols */
    public function lint(iterable $symbols): LintReport
    {
        $defects = [];
        $processed = 0;

        foreach ($symbols as $className) {
            if (!$this->isLintableClass($className)) {
                continue;
            }

            foreach ($this->lintClass(new ReflectionClass($className)) as $defect) {
                $defects[] = $defect;
            }

            ++$processed;
        }

        return new LintReport($processed, $defects);
    }

    /** Interfaces, traits, enums, and files that fail to load have no `#[Catch_]` properties to lint. */
    private function isLintableClass(string $className): bool
    {
        try {
            return class_exists($className)
                && !is_subclass_of($className, UnitEnum::class);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<MappingDefect>
     */
    private function lintClass(ReflectionClass $reflectionClass): Generator
    {
        $className = $reflectionClass->getName();

        $plan = $this->planRegistry->getPlan($className);

        // materializing the whole plan makes the compilers report every mapping they had to drop
        $planDefects = null === $plan ? [] : $this->lintPlan($className, $plan);

        $compilationDefects = $this->defectCollector->flush();

        yield from $compilationDefects;

        yield from $this->lintStructure($reflectionClass, $plan, [] !== $compilationDefects);

        yield from $planDefects;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<MappingDefect>
     */
    private function lintStructure(ReflectionClass $reflectionClass, ?ObjectExceptionMappingPlan $plan, bool $compilationFailed): Generator
    {
        $classLocation = new DefectLocation($reflectionClass->getName());

        if (!$this->hasTryAttribute($reflectionClass)) {
            if ($this->hasCatchProperties($reflectionClass)) {
                yield MappingDefect::warning(
                    'Properties declare #[Catch_] mappings, but the class is not marked with #[Try_], so it never matches anything.',
                    $classLocation,
                );
            }

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

    /**
     * @param class-string $className
     *
     * @return list<MappingDefect>
     */
    private function lintPlan(string $className, ObjectExceptionMappingPlan $plan): array
    {
        $defects = [];

        // a property whose mappings failed to compile is dropped and reported to the collector, never thrown
        foreach ($plan->getPropertyPlans() as $propertyPlan) {
            $defect = $this->compileCatchPlans($className, $propertyPlan);

            if (null !== $defect) {
                $defects[] = $defect;
            }
        }

        return $defects;
    }

    /**
     * Forces the catch plans of a property, which is what compiles every one of its `#[Catch_]` attributes.
     *
     * @param class-string $className
     */
    private function compileCatchPlans(string $className, PropertyExceptionMappingPlan $propertyPlan): ?MappingDefect
    {
        try {
            foreach ($propertyPlan->getCatchPlans() as $catchPlan) {
                unset($catchPlan);
            }
        } catch (Throwable $exception) {
            // a catch plan compiled this late is past the compiler's own guard, so it still throws
            return MappingDefect::error(new DefectLocation($className, $propertyPlan->getName()), $exception);
        }

        return null;
    }

    /** @param ReflectionClass<object> $reflectionClass */
    private function hasTryAttribute(ReflectionClass $reflectionClass): bool
    {
        return [] !== $reflectionClass->getAttributes(Try_::class);
    }

    /** @param ReflectionClass<object> $reflectionClass */
    private function hasCatchProperties(ReflectionClass $reflectionClass): bool
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($this->hasCatchAttributes($reflectionProperty)) {
                return true;
            }
        }

        return false;
    }

    private function hasCatchAttributes(ReflectionProperty $reflectionProperty): bool
    {
        return [] !== $reflectionProperty->getAttributes(Catch_::class);
    }
}
