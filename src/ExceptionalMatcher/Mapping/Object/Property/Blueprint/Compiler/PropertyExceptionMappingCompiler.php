<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionProperty;
use Reflector;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint>
 */
final class PropertyExceptionMappingCompiler implements ExceptionMappingCompiler
{
    public function __construct(
        /** @var ExceptionMappingCompiler<ReflectionAttribute<Catch_<Throwable,Throwable>>,CatchExceptionMappingBlueprint<Throwable>> */
        private readonly ExceptionMappingCompiler $catchCompiler,
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $printRegistry,
        private readonly ?LoggerInterface $errorReporter = null,
    ) {
    }

    /** @noinspection PhpUnnecessaryStaticReferenceInspection */
    public function reportingTo(LoggerInterface $reporter): static
    {
        return new self($this->catchCompiler->reportingTo($reporter), $this->printRegistry, $reporter);
    }

    /** @param ReflectionProperty $reflector */
    public function compilePrint(Reflector $reflector): ?PropertyExceptionMappingBlueprint
    {
        try {
            return $this->compile($reflector);
        } catch (Throwable $exception) {
            $e = new PropertyExceptionMappingPrintCompilationFailedException(
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

    private function compile(ReflectionProperty $reflectionProperty): ?PropertyExceptionMappingBlueprint
    {
        $catchPlans = new MemoizingIteratorAggregate($this->compileCatchPlans($reflectionProperty));

        $mappingPrint = new PropertyExceptionMappingBlueprint($reflectionProperty, $catchPlans, $this->printRegistry);

        if (!$mappingPrint->hasCatchPrints()) {
            $type = new PropertyTypeAnalyser($reflectionProperty->getType());

            if (!$type->allowsMatchableObjects($this->printRegistry)) {
                return null;
            }
        }

        return $mappingPrint;
    }

    /**
     * @psalm-suppress UnnecessaryVarAnnotation
     *
     * @return Generator<int,CatchExceptionMappingBlueprint<Throwable>>
     */
    private function compileCatchPlans(ReflectionProperty $property): Generator
    {
        /** @phpstan-var ReflectionAttribute<Catch_<Throwable,Throwable>> $catchAttribute */
        foreach ($property->getAttributes(Catch_::class) as $catchAttribute) {
            /** @psalm-suppress TooManyArguments */
            if (null !== $catchPlan = $this->catchCompiler->compilePrint($catchAttribute, $property)) { // @phpstan-ignore arguments.count
                yield $catchPlan;
            }
        }
    }
}
