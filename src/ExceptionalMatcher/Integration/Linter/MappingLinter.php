<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter;

use Generator;
use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\DefectLocation;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyPlan;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

use function sprintf;

/**
 * Checks the `#[Try_]` / `#[Catch_]` mappings of the given classes for every statically detectable error.
 *
 * The reference checks are not re-implemented here: forcing the catch plans of a property runs the very
 * same compilation the matcher runs in production, and its failures become the defect report. The linter
 * only adds the structural observations that the runtime deliberately ignores.
 *
 * @api
 */
final class MappingLinter
{
    /**
     * @template T of MatchedExceptionFormatter
     *
     * @phpstan-param ContainerInterface<class-string<T>,T> $formatterRegistry
     *
     * @psalm-param ContainerInterface<class-string<MatchedExceptionFormatter>,MatchedExceptionFormatter> $formatterRegistry
     */
    public function __construct(
        private readonly ClassMatchingPlanRegistry $planRegistry,
        private readonly ContainerInterface $formatterRegistry,
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
        $plan = $this->planRegistry->getPlan($reflectionClass->getName());

        yield from $this->lintStructure($reflectionClass, $plan);

        if (null === $plan) {
            return;
        }

        yield from $this->lintPlan($reflectionClass->getName(), $plan);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<MappingDefect>
     */
    private function lintStructure(ReflectionClass $reflectionClass, ?ClassMatchingPlan $plan): Generator
    {
        $classLocation = new DefectLocation($reflectionClass->getName());
        $hasCatchProperties = $this->hasCatchProperties($reflectionClass);

        if (null === $plan) {
            if ($hasCatchProperties) {
                yield MappingDefect::error(
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
        }

        if (!$hasCatchProperties) {
            yield MappingDefect::warning(
                '#[Try_] class declares no #[Catch_] properties; it only matches through nested objects or iterable items.',
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
                if ([] === $parentProperty->getAttributes(Catch_::class)) {
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
     * @return Generator<MappingDefect>
     */
    private function lintPlan(string $className, ClassMatchingPlan $plan): Generator
    {
        try {
            foreach ($plan->getPropertyPlans() as $propertyPlan) {
                yield from $this->lintPropertyPlan($className, $propertyPlan);
            }
        } catch (Throwable $exception) {
            // materializing the next property plan failed - the exact property is unknown here
            yield MappingDefect::error($exception->getMessage(), new DefectLocation($className), $exception);
        }
    }

    /**
     * @param class-string $className
     *
     * @return Generator<MappingDefect>
     */
    private function lintPropertyPlan(string $className, PropertyPlan $propertyPlan): Generator
    {
        $propertyLocation = new DefectLocation($className, $propertyPlan->getName());

        try {
            foreach ($propertyPlan->getCatchPlans() as $catchPlan) {
                $formatterId = $catchPlan->getFormatterId();

                if (!$this->formatterRegistry->has($formatterId)) {
                    yield MappingDefect::warning(
                        sprintf('Formatter "%s" is not registered in the formatter registry.', $formatterId),
                        $propertyLocation,
                    );
                }
            }
        } catch (Throwable $exception) {
            yield MappingDefect::error($exception->getMessage(), $propertyLocation, $exception);
        }
    }

    /** @param ReflectionClass<object> $reflectionClass */
    private function hasCatchProperties(ReflectionClass $reflectionClass): bool
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ([] !== $reflectionProperty->getAttributes(Catch_::class)) {
                return true;
            }
        }

        return false;
    }
}
