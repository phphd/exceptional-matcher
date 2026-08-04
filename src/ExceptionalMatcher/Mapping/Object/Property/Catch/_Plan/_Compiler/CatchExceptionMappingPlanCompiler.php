<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception\UnregisteredFormatterException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionPlan;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class CatchExceptionMappingPlanCompiler
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
        private readonly bool $throwOnFailure = true,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /** @param ReflectionAttribute<Catch_<Throwable,Throwable>> $catchAttribute */
    public function compilePlan(ReflectionAttribute $catchAttribute): ?CatchExceptionMappingPlan
    {
        try {
            return $this->compile($catchAttribute);
        } catch (Throwable $exception) {
            $e = new CatchExceptionMappingPlanCompilationFailedException($exception);

            if (!$this->throwOnFailure) {
                // One broken #[Catch_] won't spoil the whole match tree.
                $this->logger?->error($e->getMessage(), ['exception' => $e]);

                return null;
            }

            throw $e;
        }
    }

    private function compile(ReflectionAttribute $catchAttribute): CatchExceptionMappingPlan
    {
        $catch = $this->instantiateCatch($catchAttribute);

        return new CatchExceptionMappingPlan(
            $this->compileConditionPlan($catch),
            $this->compileFormatter($catch),
            $catch->getMessage(),
        );
    }

    /**
     * @param ReflectionAttribute<Catch_<Throwable,Throwable>> $catchAttribute
     *
     * @return Catch_<Throwable,Throwable>
     */
    private function instantiateCatch(ReflectionAttribute $catchAttribute): Catch_
    {
        try {
            return $catchAttribute->newInstance();
        } catch (Throwable $e) {
            throw new CatchAttributeInstantiationFailedException($e);
        }
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

    /** @param Catch_<Throwable,Throwable> $catch */
    private function compileConditionPlan(Catch_ $catch): MatchConditionPlan
    {
        $conditionPlan = $this->matchConditionCompiler->compile($catch);

        Assert::notNull($conditionPlan, 'Condition compiler must produce a plan.');

        return $conditionPlan;
    }
}
