<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler;

use Generator;
use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMappingPlanCompiler<ReflectionClass<object>,ObjectExceptionMappingPlan<object>>
 */
final class ObjectExceptionMappingPlanCompiler implements ExceptionMappingPlanCompiler
{
    public function __construct(
        /** @var ExceptionMappingPlanCompiler<ReflectionProperty,PropertyExceptionMappingPlan> */
        private readonly ExceptionMappingPlanCompiler $propertyMappingPlanCompiler,
        private readonly ?LoggerInterface $errorReporter = null,
    ) {
    }

    public function reportingTo(LoggerInterface $reporter): self
    {
        return new self($this->propertyMappingPlanCompiler->reportingTo($reporter), $reporter);
    }

    /** @param ReflectionClass<object> $reflector */
    public function compilePlan(Reflector $reflector): ?ObjectExceptionMappingPlan
    {
        try {
            return $this->compile($reflector);
        } catch (Throwable $exception) {
            $e = new ObjectExceptionMappingPlanCompilationFailedException($reflector->getName(), $exception);

            if (null !== $this->errorReporter) {
                // One broken class mapping won't spoil the whole situation.
                $this->errorReporter->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return ?ObjectExceptionMappingPlan<object>
     */
    private function compile(ReflectionClass $reflectionClass): ?ObjectExceptionMappingPlan
    {
        if ([] === $reflectionClass->getAttributes(Try_::class)) {
            return null;
        }

        $mappingPlan = new ObjectExceptionMappingPlan(
            $reflectionClass->getName(),
            new ReusableIteratorAggregate($this->compilePropertyPlans($reflectionClass)),
        );

        if (!$mappingPlan->hasPropertyPlans()) {
            throw new LogicException('#[Try_] class does not define any #[Catch_] mappings and no nested matchable properties, so it never matches anything.');
        }

        return $mappingPlan;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<PropertyExceptionMappingPlan>
     */
    private function compilePropertyPlans(ReflectionClass $reflectionClass): Generator
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyPlan = $this->propertyMappingPlanCompiler->compilePlan($reflectionProperty);

            if (null === $propertyPlan) {
                continue;
            }

            yield $propertyPlan;
        }
    }
}
