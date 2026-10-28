<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
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
 * @implements ExceptionMappingCompiler<ReflectionAttribute<Catch_<Throwable,Throwable>>,CatchExceptionMappingPlan<Throwable>>
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
    public function compilePlan(Reflector $reflector, ?ReflectionProperty $property = null): ?CatchExceptionMappingPlan
    {
        Assert::notNull($property);

        try {
            $catch = $this->instantiateCatch($reflector);

            return $this->compile($catch);
        } catch (Throwable $exception) {
            $e = new CatchExceptionMappingPlanCompilationFailedException(
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
     * @return CatchExceptionMappingPlan<Throwable>
     */
    private function compile(Catch_ $catch): CatchExceptionMappingPlan
    {
        return new CatchExceptionMappingPlan(
            $this->compileMatchConditionPlan($catch),
            $this->compileExceptionFormatter($catch),
            $catch->getMessage(),
        );
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @return MatchConditionPlan<Throwable>
     */
    private function compileMatchConditionPlan(Catch_ $catch): MatchConditionPlan
    {
        $conditionPlan = $this->matchConditionCompiler->compile($catch);

        Assert::notNull($conditionPlan, 'Condition compiler must produce a plan.');

        return $conditionPlan;
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
