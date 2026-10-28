<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\PreCompiledMatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\QualifiedMatchedExceptionFormatter;
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
     * @template T of MatchedExceptionFormatter
     *
     * @phpstan-param ContainerInterface<class-string<T>,T> $exceptionFormatterRegistry
     *
     * @psalm-param ContainerInterface<class-string<MatchedExceptionFormatter>,MatchedExceptionFormatter> $exceptionFormatterRegistry
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
    public function compilePrint(Reflector $reflector, ?ReflectionProperty $property = null): ?CatchExceptionMappingBlueprint
    {
        Assert::notNull($property);

        try {
            $catch = $this->instantiateCatch($reflector);

            return $this->compile($catch);
        } catch (Throwable $exception) {
            $e = new CatchExceptionMappingPrintCompilationFailedException(
                $property->getDeclaringClass()
                    ->getName(),
                $property->getName(),
                $exception,
            );

            if (null !== $this->errorReporter) {
                // One broken #[Catch_] won't spoil the whole match tree.
                $this->errorReporter->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
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
    private function compile(Catch_ $catch): CatchExceptionMappingBlueprint
    {
        return new CatchExceptionMappingBlueprint(
            $this->compileMatchConditionPrint($catch),
            $this->compileExceptionFormatter($catch),
            $catch->getMessage(),
        );
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return MatchConditionBlueprint<Throwable>
     */
    private function compileMatchConditionPrint(Catch_ $catch): MatchConditionBlueprint
    {
        $conditionPrint = $this->matchConditionCompiler->compile($catch);

        Assert::notNull($conditionPrint, 'Condition compiler must produce a blueprint.');

        if ($conditionPrint instanceof PreCompiledMatchConditionBlueprint && $conditionPrint->bind() instanceof FalseCondition) {
            throw new LogicException('#[Catch_] match condition is pre-compiled to false, so it never matches anything.');
        }

        return $conditionPrint;
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @phpstan-return ?class-string<MatchedExceptionFormatter<Throwable,mixed>>
     *
     * @psalm-return ?class-string<MatchedExceptionFormatter>
     */
    private function compileExceptionFormatter(Catch_ $catch): ?string
    {
        $formatterId = $catch->getFormat();

        if (null === $formatterId) {
            return null;
        }

        $formatter = new QualifiedMatchedExceptionFormatter($this->exceptionFormatterRegistry);

        $formatter->getFormatter($formatterId);

        return $formatterId;
    }
}
