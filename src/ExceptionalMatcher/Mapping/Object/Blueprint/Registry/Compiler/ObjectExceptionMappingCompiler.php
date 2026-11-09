<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler;

use Closure;
use Generator;
use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMappingCompiler<ReflectionClass<object>,ObjectExceptionMappingBlueprint<object>>
 */
final class ObjectExceptionMappingCompiler implements ExceptionMappingCompiler
{
    public function __construct(
        /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> */
        private readonly ExceptionMappingCompiler $propertyCompiler,
        private readonly ?LoggerInterface $errorReporter = null,
    ) {
    }

    /** @noinspection PhpUnnecessaryStaticReferenceInspection */
    public function reportingTo(LoggerInterface $reporter): static
    {
        return new self($this->propertyCompiler->reportingTo($reporter), $reporter);
    }

    /** @param ReflectionClass<object> $reflector */
    public function compile(Reflector $reflector): ?ObjectExceptionMappingBlueprint
    {
        return $this->except($this->doCompile(...), $reflector);
    }

    /** @param ObjectExceptionMappingBlueprint<object> $blueprint */
    public function forceFullCompilation(ExceptionMappingBlueprint $blueprint): void
    {
        $this->except(
            function () use ($blueprint) {
                foreach ($blueprint->getPropertyPrints() as $propertyPrint) {
                    $this->propertyCompiler->forceFullCompilation($propertyPrint);
                }
            },
            new ReflectionClass($blueprint->getClassName()),
        );
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return ?ObjectExceptionMappingBlueprint<object>
     */
    private function doCompile(ReflectionClass $reflectionClass): ?ObjectExceptionMappingBlueprint
    {
        if ([] === $reflectionClass->getAttributes(Try_::class)) {
            return null;
        }

        if ($reflectionClass->isAbstract()) {
            throw new LogicException('#[Try_] on an abstract class has no effect: the attribute is not inherited by the subclasses.');
        }

        $objectPrint = new ObjectExceptionMappingBlueprint(
            $reflectionClass->getName(),
            new MemoizingIteratorAggregate($this->compilePropertyPrints($reflectionClass)),
        );

        if (!$objectPrint->hasPropertyPrints()) {
            throw new LogicException('#[Try_] class does not define any #[Catch_] mappings and no nested matchable properties, so it never matches anything.');
        }

        return $objectPrint;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return Generator<PropertyExceptionMappingBlueprint>
     */
    private function compilePropertyPrints(ReflectionClass $reflectionClass): Generator
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (null !== $propertyPrint = $this->propertyCompiler->compile($reflectionProperty)) {
                yield $propertyPrint;
            }
        }
    }

    /**
     * @template T
     *
     * @param Closure(ReflectionClass<object>):T $closure
     * @param ReflectionClass<object> $class
     *
     * @return ?T
     */
    private function except(Closure $closure, ReflectionClass $class): mixed
    {
        try {
            return $closure($class);
        } catch (Throwable $exception) {
            $e = new ObjectExceptionMappingPrintCompilationFailedException($class, $exception);

            if (null !== $this->errorReporter) {
                // One broken class mapping won't spoil the whole situation.
                $this->errorReporter->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }
}
