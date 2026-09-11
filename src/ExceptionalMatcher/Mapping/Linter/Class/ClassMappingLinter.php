<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use AppendIterator;
use Generator;
use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\PreCompiledMatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

use function sprintf;

/**
 * @internal
 *
 * @implements MappingLinter<class-string,LintReport>
 */
final class ClassMappingLinter implements MappingLinter
{
    /** @api */
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

        /** @var AppendIterator<int,MappingDefect,Iterator<MappingDefect>> $defects */
        $defects = new AppendIterator();

        foreach ($symbols as $className) {
            if (!$this->loadClass($className)) {
                continue;
            }

            $defects->append($this->lintClass(new ReflectionClass($className)));

            ++$processed;
        }

        return new LintReport($processed, new ReusableIteratorAggregate($defects));
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<MappingDefect>
     */
    private function lintClass(ReflectionClass $reflectionClass): Generator
    {
        yield from $this->lintPlan($reflectionClass);

        yield from $this->lintStructure($reflectionClass);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return iterable<MappingDefect>
     */
    private function lintPlan(ReflectionClass $reflectionClass): iterable
    {
        if (!$this->planRegistry->hasPlan($reflectionClass->getName())) {
            if ([] !== $compilationDefects = $this->defectCollector->flush()) {
                return yield from $compilationDefects;
            }

            return yield from $this->possiblyMissingTryAttribute($reflectionClass);
        }

        /** @var ObjectExceptionMappingPlan<object> $plan */
        $plan = $this->planRegistry->getPlan($reflectionClass->getName());

        // Compilation is done lazily through traversal
        foreach ($plan->getPropertyPlans() as $propertyPlan) {
            foreach ($propertyPlan->getCatchPlans() as $catchPlan) {
                $conditionPlan = $catchPlan->getConditionPlan();

                if ($conditionPlan instanceof PreCompiledMatchConditionPlan) {
                    continue;
                }

                if (!$conditionPlan instanceof CompositeMatchConditionPlan) {
                    yield MappingDefect::notice(
                        '#[Catch_] condition plan is not composite, so it cannot be linted.',
                        DefectLocation::ofProperty($propertyPlan->getProperty()),
                    );

                    continue;
                }

                try {
                    foreach ($conditionPlan->getPlans() as $conditionSubPlan) {
                        unset($conditionSubPlan);
                    }
                } catch (Throwable $e) {
                    yield MappingDefect::error(
                        DefectLocation::ofProperty($propertyPlan->getProperty()),
                        $e,
                    );
                }

                unset($catchPlan);
            }
            unset($propertyPlan);
        }

        yield from $this->defectCollector->flush();
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return list<MappingDefect>
     */
    private function possiblyMissingTryAttribute(ReflectionClass $reflectionClass): array
    {
        // The class plan is null, but it might be so due to a missing #[Try_] attribute
        $missingTryDefects = [];

        foreach ($reflectionClass->getProperties() as $property) {
            // if class's property has a catch plan, and class has no plan, - that's a defect
            if (true !== $this->propertyMappingPlanCompiler->compilePlan($property)?->hasCatchPlans()) {
                continue;
            }

            $missingTryDefects[] = MappingDefect::warning(
                'Properties declare #[Catch_] mappings, but the class is not marked with #[Try_], so it never matches anything.',
                DefectLocation::ofProperty($property),
            );
        }

        return $missingTryDefects;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<MappingDefect>
     */
    private function lintStructure(ReflectionClass $reflectionClass): Generator
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

    /** @param class-string $className */
    private function loadClass(string $className): bool
    {
        try {
            if (!class_exists($className)) {
                return false;
            }
        } catch (\ErrorException $e) {
            return false;
        }

        return true;
    }
}
