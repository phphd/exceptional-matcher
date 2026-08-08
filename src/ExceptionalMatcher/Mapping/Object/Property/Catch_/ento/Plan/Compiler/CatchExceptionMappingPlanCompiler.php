<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Plan\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\ento\Plan\Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\ento\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\ento\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Plan\Compiler\_Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Plan\Compiler\_Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Plan\Compiler\_Exception\UnregisteredFormatterException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ento\Formatter\MatchedExceptionFormatter;
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
 * @implements ExceptionMappingPlanCompiler<ReflectionAttribute<Catch_<Throwable,Throwable>>,CatchExceptionMappingPlan<Throwable>>
 */
final class CatchExceptionMappingPlanCompiler implements ExceptionMappingPlanCompiler
{
    /**
     * @template T of MatchedExceptionFormatter
     *
     * @phpstan-param ContainerInterface<class-string<T>,T> $formatterRegistry
     *
     * @psalm-param ContainerInterface<class-string<MatchedExceptionFormatter>,MatchedExceptionFormatter> $formatterRegistry
     */
    public function __construct(
        /** @var MatchConditionCompiler<Throwable> */
        private readonly MatchConditionCompiler $matchConditionCompiler,
        private readonly ContainerInterface $formatterRegistry,
        private readonly ?LoggerInterface $errorReporter = null,
    ) {
    }

    public function reportingTo(LoggerInterface $reporter): self
    {
        return new self($this->matchConditionCompiler, $this->formatterRegistry, $reporter);
    }

    /** @param ReflectionAttribute<Catch_<Throwable,Throwable>> $reflector */
    public function compilePlan(Reflector $reflector, ?ReflectionProperty $property = null): ?CatchExceptionMappingPlan
    {
        try {
            $catch = $this->instantiateCatch($reflector);

            return $this->compile($catch);
        } catch (Throwable $exception) {
            $e = new CatchExceptionMappingPlanCompilationFailedException(
                $property?->getDeclaringClass()
                    ->getName(),
                $property?->getName(),
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

    /** @param Catch_<Throwable,Throwable> $catch */
    private function compile(Catch_ $catch): CatchExceptionMappingPlan
    {
        return new CatchExceptionMappingPlan(
            $this->compileConditionPlan($catch),
            $this->compileFormatter($catch),
            $catch->getMessage(),
        );
    }

    /** @param Catch_<Throwable,Throwable> $catch */
    private function compileConditionPlan(Catch_ $catch): MatchConditionPlan
    {
        $conditionPlan = $this->matchConditionCompiler->compile($catch);

        Assert::notNull($conditionPlan, 'Condition compiler must produce a plan.');

        return $conditionPlan;
    }

    /**
     * @param Catch_<Throwable,Throwable> $catch
     *
     * @phpstan-return class-string<MatchedExceptionFormatter<Throwable,mixed>>
     *
     * @psalm-return class-string<MatchedExceptionFormatter>
     */
    private function compileFormatter(Catch_ $catch): string
    {
        $formatterId = $catch->getFormat();

        if (!$this->formatterRegistry->has($formatterId)) {
            throw new UnregisteredFormatterException($formatterId);
        }

        return $formatterId;
    }
}
