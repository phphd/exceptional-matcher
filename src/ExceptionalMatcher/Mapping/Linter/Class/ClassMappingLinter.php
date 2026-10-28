<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Class\ClassReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
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
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $planRegistry,
        /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> */
        private readonly ExceptionMappingCompiler $propertyMappingCompiler,
        private readonly CompilationDefectCollector $compilationDefectCollector,
    ) {
    }

    /** @param iterable<class-string> $symbols */
    public function lint(iterable $symbols): LintReport
    {
        $classReports = $this->lintClasses($symbols);

        return new LintReport(
            iterator_to_array($classReports, preserve_keys: false),
            $classReports->getReturn(),
        );
    }

    /**
     * A scanned class may hold defects of an inherited property, which belong to the class declaring it,
     * so every class is scanned before any of them is reported on.
     *
     * @param iterable<class-string> $classNames
     *
     * @return Generator<int,ClassReport,never,int>
     */
    private function lintClasses(iterable $classNames): Generator
    {
        /** @var array<class-string,non-empty-list<MappingDefect>> $classDefects */
        $classDefects = [];
        $scannedSymbols = 0;

        foreach ($classNames as $className) {
            foreach ($this->lintClass($className) as $defect) {
                $classDefects[$defect->getLocation()
                    ->getClassName()][] = $defect;
            }

            ++$scannedSymbols;
        }

        foreach ($classDefects as $className => $defects) {
            yield new ClassReport($className, $defects);
        }

        return $scannedSymbols;
    }

    /**
     * @param class-string $className
     *
     * @return Generator<MappingDefect>
     */
    private function lintClass(string $className): Generator
    {
        $reflectionClass = new ReflectionClass($className);

        yield from $this->lintStructure($reflectionClass);

        yield from $this->lintClassPlan($reflectionClass);
    }

    /**
     * @psalm-suppress NullableReturnStatement
     *
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return iterable<MappingDefect>
     */
    private function lintClassPlan(ReflectionClass $reflectionClass): iterable
    {
        if (null !== $print = $this->planRegistry->getMappingBlueprint($reflectionClass->getName())) {
            return yield from $this->lintObjectMappingPrint($print);
        }

        // If no print, it possibly has failed compilation:
        if ([] !== $compilationDefects = $this->compilationDefectCollector->flushDefects()) {
            return yield from $compilationDefects;
        }

        // Or lacks #[Try] attribute:
        return yield from $this->possiblyMissingTryAttribute($reflectionClass);
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
            if (true !== $this->propertyMappingCompiler->compilePrint($property)?->hasCatchPrints()) {
                continue;
            }

            // If class's property has a catch plan,
            // but the class itself has no plan, that's a defect
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
        if (!$this->hasTryAttribute($reflectionClass)) {
            return;
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
                if ($this->hasCatchAttributes($parentProperty)) {
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

    /**
     * @param ObjectExceptionMappingBlueprint<object> $objectPrint
     *
     * @return Generator<MappingDefect>
     */
    private function lintObjectMappingPrint(ObjectExceptionMappingBlueprint $objectPrint): Generator
    {
        // Compilation is done lazily through traversal
        foreach ($objectPrint->getPropertyPrints() as $propertyPrint) {
            foreach ($propertyPrint->getCatchPrints() as $catchPrint) {
                if (true === yield from $this->traverseMatchConditionPlan($propertyPrint, $catchPrint->getConditionPlan())) {
                    continue;
                }

                yield MappingDefect::notice(
                    '#[Catch_] condition plan is not composite, so it cannot be linted.',
                    DefectLocation::ofProperty($propertyPrint->getProperty()),
                );
            }
        }

        yield from $this->compilationDefectCollector->flushDefects();
    }

    /**
     * @param MatchConditionPlan<Throwable> $matchConditionPlan
     *
     * @return Generator<MappingDefect>
     */
    private function traverseMatchConditionPlan(PropertyExceptionMappingBlueprint $propertyPlan, MatchConditionPlan $matchConditionPlan): Generator
    {
        if (!$matchConditionPlan instanceof CompositeMatchConditionPlan) {
            return false;
        }

        try {
            // Traversal compiles the match conditions' plans
            foreach ($matchConditionPlan->getPlans() as $subPlan) {
                yield from $this->traverseMatchConditionPlan($propertyPlan, $subPlan); // recurse
            }
        } catch (Throwable $e) {
            yield MappingDefect::error(
                DefectLocation::ofProperty($propertyPlan->getProperty()),
                $e,
            );
        }

        return true;
    }
}
