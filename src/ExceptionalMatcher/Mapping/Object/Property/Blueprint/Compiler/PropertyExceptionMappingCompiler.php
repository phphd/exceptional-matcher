<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler;

use Closure;
use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Type\PossiblyExceptionMappedType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\PropertyType;
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
    private readonly PossiblyExceptionMappedType $possiblyExceptionMappedType;

    public function __construct(
        /** @var ExceptionMappingCompiler<ReflectionAttribute<Catch_<Throwable,Throwable>>,CatchExceptionMappingBlueprint<Throwable>> */
        private readonly ExceptionMappingCompiler $catchCompiler,
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $printRegistry,
        private readonly ?LoggerInterface $errorReporter = null,
    ) {
        $this->possiblyExceptionMappedType = new PossiblyExceptionMappedType($this->printRegistry);
    }

    /** @noinspection PhpUnnecessaryStaticReferenceInspection */
    public function reportingTo(LoggerInterface $reporter): static
    {
        return new self($this->catchCompiler->reportingTo($reporter), $this->printRegistry, $reporter);
    }

    /** @param ReflectionProperty $reflector */
    public function compile(Reflector $reflector): ?PropertyExceptionMappingBlueprint
    {
        return $this->except($this->doCompile(...), $reflector);
    }

    /** @param PropertyExceptionMappingBlueprint $blueprint */
    public function forceFullCompilation(ExceptionMappingBlueprint $blueprint): void
    {
        $this->except(function (ReflectionProperty $property) use ($blueprint): void {
            /** @psalm-suppress TooManyArguments */
            foreach ($blueprint->getCatchPrints() as $catchPrint) {
                $this->catchCompiler->forceFullCompilation($catchPrint, $property); // @phpstan-ignore arguments.count
            }
        }, $blueprint->getProperty());
    }

    private function doCompile(ReflectionProperty $property): ?PropertyExceptionMappingBlueprint
    {
        $catchPrints = new MemoizingIteratorAggregate($this->compileCatchPrints($property));

        $propertyPrint = new PropertyExceptionMappingBlueprint($property, $catchPrints, $this->printRegistry);

        if (!$propertyPrint->hasCatchPrints()
            && !$this->propertyType($property)->includes($this->possiblyExceptionMappedType)) {
            return null;
        }

        return $propertyPrint;
    }

    /**
     * @psalm-suppress UnnecessaryVarAnnotation
     *
     * @return Generator<int,CatchExceptionMappingBlueprint<Throwable>>
     */
    private function compileCatchPrints(ReflectionProperty $property): Generator
    {
        /** @phpstan-var ReflectionAttribute<Catch_<Throwable,Throwable>> $catchAttribute */
        foreach ($property->getAttributes(Catch_::class) as $catchAttribute) {
            /** @psalm-suppress TooManyArguments */
            if (null !== $catchPrint = $this->catchCompiler->compile($catchAttribute, $property)) { // @phpstan-ignore arguments.count
                yield $catchPrint;
            }
        }
    }

    private function propertyType(ReflectionProperty $property): PropertyType
    {
        return new PropertyType($property->getType());
    }

    /**
     * @template T
     *
     * @param Closure(ReflectionProperty):T $closure
     *
     * @return ?T
     */
    private function except(Closure $closure, ReflectionProperty $property): mixed
    {
        try {
            return $closure($property);
        } catch (Throwable $exception) {
            $e = new PropertyExceptionMappingPrintCompilationFailedException($property, $exception);

            if (null !== $this->errorReporter) {
                // One broken property won't spoil the whole match model.
                $this->errorReporter->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }
}
