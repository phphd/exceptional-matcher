<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler;

use Closure;
use LogicException;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionMapper\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\PreCompiledMatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\QualifiedExceptionMatchFormatter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionProperty;
use Reflector;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements ExceptionMappingCompiler<ReflectionAttribute<Catch_<Throwable,Throwable>>,CatchExceptionMappingBlueprint<Throwable>>
 */
final class CatchExceptionMappingCompiler implements ExceptionMappingCompiler
{
    /**
     * @template T of ExceptionMatchFormatter
     *
     * @phpstan-param ContainerInterface<class-string<T>,T> $exceptionFormatterRegistry
     *
     * @psalm-param ContainerInterface<class-string<ExceptionMatchFormatter>,ExceptionMatchFormatter> $exceptionFormatterRegistry
     */
    public function __construct(
        /** @var MatchConditionCompiler<Throwable> */
        private readonly MatchConditionCompiler $matchConditionCompiler,
        private readonly ContainerInterface $exceptionFormatterRegistry,
        private readonly ?LoggerInterface $errorReporter = null,
    ) {
    }

    /** @noinspection PhpUnnecessaryStaticReferenceInspection */
    public function reportingTo(LoggerInterface $reporter): static
    {
        return new self($this->matchConditionCompiler, $this->exceptionFormatterRegistry, $reporter);
    }

    /** @param ReflectionAttribute<Catch_<Throwable,Throwable>> $reflector */
    public function compile(Reflector $reflector, ?ReflectionProperty $property = null): ?CatchExceptionMappingBlueprint
    {
        Assert::notNull($property);

        return $this->except(function (ReflectionProperty $property) use ($reflector): CatchExceptionMappingBlueprint {
            $catch = $this->instantiateCatch($reflector);

            return $this->doCompile($property, $catch);
        }, $property);
    }

    /** @param CatchExceptionMappingBlueprint<Throwable> $blueprint */
    public function forceFullCompilation(ExceptionMappingBlueprint $blueprint, ?ReflectionProperty $property = null): void
    {
        Assert::notNull($property);

        $this->except(function () use ($blueprint): void {
            $conditionPrint = $blueprint->getConditionPrint();

            if (!$conditionPrint instanceof CompositeMatchConditionBlueprint) {
                throw new LogicException('#[Catch_] condition print is not composite, so it cannot be pre-compiled');
            }

            $conditionsCount = $conditionPrint->forceCompleteCompilation();

            if (0 === $conditionsCount) {
                throw new LogicException('#[Catch_] condition is compiled to an empty condition, so it matches any exception');
            }
        }, $property);
    }

    /**
     * @param ReflectionAttribute<Catch_<Throwable,Throwable>> $reflector
     *
     * @return Catch_<Throwable,Throwable>
     */
    private function instantiateCatch(ReflectionAttribute $reflector): Catch_
    {
        try {
            return $reflector->newInstance();
        } catch (Throwable $e) {
            throw new CatchAttributeInstantiationFailedException($e);
        }
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return CatchExceptionMappingBlueprint<Throwable>
     */
    private function doCompile(ReflectionProperty $property, Catch_ $catch): CatchExceptionMappingBlueprint
    {
        return new CatchExceptionMappingBlueprint(
            $this->compileMatchConditionPrint($property, $catch),
            $this->compileExceptionFormatter($catch),
            $catch->getMessage(),
        );
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return MatchConditionBlueprint<Throwable>
     */
    private function compileMatchConditionPrint(ReflectionProperty $property, Catch_ $catch): MatchConditionBlueprint
    {
        $conditionPrint = $this->matchConditionCompiler->compile($property, $catch);

        Assert::notNull($conditionPrint, 'Condition compiler must produce a blueprint.');

        if ($conditionPrint instanceof PreCompiledMatchConditionBlueprint && $conditionPrint->bind() instanceof FalseCondition) {
            throw new LogicException('#[Catch_] match condition is pre-compiled to false, so it never matches anything.');
        }

        return $conditionPrint;
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @phpstan-return ?class-string<ExceptionMatchFormatter<Throwable,mixed>>
     *
     * @psalm-return ?class-string<ExceptionMatchFormatter>
     */
    private function compileExceptionFormatter(Catch_ $catch): ?string
    {
        $formatterId = $catch->getFormat();

        if (null === $formatterId) {
            return null;
        }

        $formatter = new QualifiedExceptionMatchFormatter($this->exceptionFormatterRegistry);

        $formatter->getFormatter($formatterId);

        return $formatterId;
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
            $e = new CatchExceptionMappingPrintCompilationFailedException($property, $exception);

            if (null !== $this->errorReporter) {
                // One broken #[Catch_] won't spoil the whole match model.
                $this->errorReporter->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }
}
