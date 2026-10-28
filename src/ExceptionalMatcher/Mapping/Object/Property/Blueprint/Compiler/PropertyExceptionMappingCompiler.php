<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionProperty;
use Reflector;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingPlan>
 */
final class PropertyExceptionMappingCompiler implements ExceptionMappingCompiler
{
    public function __construct(
        /** @var ExceptionMappingCompiler<ReflectionAttribute<Catch_<Throwable,Throwable>>,CatchExceptionMappingPlan<Throwable>> */
        private readonly ExceptionMappingCompiler $catchPlanCompiler,
        /** @var ObjectExceptionMappingPlanRegistry<object> */
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
        private readonly ?LoggerInterface $errorReporter = null,
    ) {
    }

    /** @noinspection PhpUnnecessaryStaticReferenceInspection */
    public function reportingTo(LoggerInterface $reporter): static
    {
        return new self($this->catchPlanCompiler->reportingTo($reporter), $this->planRegistry, $reporter);
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

            if (null !== $this->errorReporter) {
                // One broken property won't spoil the whole match tree.
                $this->errorReporter->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }

    private function compile(ReflectionProperty $reflectionProperty): ?PropertyExceptionMappingPlan
    {
        $catchPlans = new MemoizingIteratorAggregate($this->compileCatchPlans($reflectionProperty));

        $propertyMappingPlan = new PropertyExceptionMappingPlan($reflectionProperty, $catchPlans, $this->planRegistry);

        if (!$propertyMappingPlan->hasCatchPlans()) {
            $type = new PropertyTypeAnalyser($reflectionProperty->getType());

            if (!$type->allowsMatchableObjects($this->planRegistry)) {
                return null;
            }
        }

        return $propertyMappingPlan;
    }

    /**
     * @psalm-suppress UnnecessaryVarAnnotation
     *
     * @return Generator<int,CatchExceptionMappingPlan<Throwable>>
     */
    private function compileCatchPlans(ReflectionProperty $property): Generator
    {
        /** @phpstan-var ReflectionAttribute<Catch_<Throwable,Throwable>> $catchAttribute */
        foreach ($property->getAttributes(Catch_::class) as $catchAttribute) {
            /** @psalm-suppress TooManyArguments */
            if (null !== $catchPlan = $this->catchPlanCompiler->compilePlan($catchAttribute, $property)) { // @phpstan-ignore arguments.count
                yield $catchPlan;
            }
        }
    }
}
