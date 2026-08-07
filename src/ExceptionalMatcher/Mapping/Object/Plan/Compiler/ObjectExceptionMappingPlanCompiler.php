<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\_Plan\_Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Composite\ReusableIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Compiler\_Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
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
            return null;
        }

        return $mappingPlan;
    }

    /** @return Generator<int,PropertyExceptionMappingPlan> */
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
