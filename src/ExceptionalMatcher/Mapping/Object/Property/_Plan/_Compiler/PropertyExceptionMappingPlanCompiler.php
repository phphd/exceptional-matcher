<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\_Plan\_Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\_Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionProperty;
use Reflector;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMappingPlanCompiler<ReflectionProperty,PropertyExceptionMappingPlan>
 */
final class PropertyExceptionMappingPlanCompiler implements ExceptionMappingPlanCompiler
{
    public function __construct(
        /** @var ExceptionMappingPlanCompiler<ReflectionAttribute<Catch_<Throwable,Throwable>>,CatchExceptionMappingPlan<Throwable>> */
        private readonly ExceptionMappingPlanCompiler $catchPlanCompiler,
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
        private readonly bool $throwOnFailure = true,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /** @param ReflectionProperty $reflector */
    public function compilePlan(Reflector $reflector): ?PropertyExceptionMappingPlan
    {
        try {
            return $this->compile($reflector);
        } catch (Throwable $exception) {
            $e = new PropertyExceptionMappingPlanCompilationFailedException(
                $reflector->getDeclaringClass()
                    ->getName(),
                $reflector->getName(),
                $exception,
            );

            if (!$this->throwOnFailure) {
                // One broken property won't spoil the whole match tree.
                $this->logger?->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }

    private function compile(ReflectionProperty $reflectionProperty): ?PropertyExceptionMappingPlan
    {
        $catchPlans = new ReusableIteratorAggregate($this->compileCatchPlans($reflectionProperty));

        $propertyMappingPlan = new PropertyExceptionMappingPlan($reflectionProperty, $catchPlans, $this->planRegistry);

        if (!$propertyMappingPlan->hasCatchPlans()) {
            $type = new PropertyTypeAnalyser($reflectionProperty->getType());

            if (!$type->allowsMatchableObjects($this->planRegistry)) {
                return null;
            }
        }

        return $propertyMappingPlan;
    }

    /** @return Generator<int,CatchExceptionMappingPlan<Throwable>> */
    private function compileCatchPlans(ReflectionProperty $property): Generator
    {
        foreach ($property->getAttributes(Catch_::class) as $catchAttribute) {
            $catchPlan = $this->catchPlanCompiler->compilePlan($catchAttribute);

            if (null === $catchPlan) {
                continue;
            }

            yield $catchPlan;
        }
    }
}
