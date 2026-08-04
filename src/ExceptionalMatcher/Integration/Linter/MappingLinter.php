<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter;

use Generator;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\DefectLocation;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

use function sprintf;

/**
 * Checks the `#[Try_]` / `#[Catch_]` mappings of the given classes for every statically detectable error.
 *
 * The reference checks are not re-implemented here: forcing the plan of a class runs the very same
 * compilation the matcher runs in production, only with `throwOnFailure` off, so the compilers report every
 * mapping they had to drop instead of aborting at the first one. Those reports are the defect collector's
 * records. The linter only adds the structural observations that the runtime deliberately ignores.
 *
 * @api
 */
final class MappingLinter
{
    public function __construct(
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
        private readonly MappingDefectCollector $defectCollector,
    ) {
    }

    /**
     * @param iterable<class-string> $classNames
     *
     * @return list<MappingDefect>
     */
    public function lint(iterable $classNames): array
    {
        $defects = [];

        foreach ($classNames as $className) {
            foreach ($this->lintClass(new ReflectionClass($className)) as $defect) {
                $defects[] = $defect;
            }
        }

        return $defects;
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
