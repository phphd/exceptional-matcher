<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class CatchExceptionMappingPlanCompiler
{
    public function __construct(
        /** @var MatchConditionCompiler<Throwable> */
        private readonly MatchConditionCompiler $matchConditionCompiler,
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

        $conditionPlan = $this->matchConditionCompiler->compile($catch);

        Assert::notNull($conditionPlan, 'Condition compiler must produce a plan.');

        return new CatchExceptionMappingPlan($conditionPlan, $catch->getFormat(), $catch->getMessage());
    }

    /** @param ReflectionAttribute<Catch_<Throwable,Throwable>> $catchAttribute */
    private function instantiateCatch(ReflectionAttribute $catchAttribute): ?Catch_
    {
        try {
            return $catchAttribute->newInstance();
        } catch (Throwable $e) {
            throw new CatchAttributeInstantiationFailedException($e);
        }
    }
}
