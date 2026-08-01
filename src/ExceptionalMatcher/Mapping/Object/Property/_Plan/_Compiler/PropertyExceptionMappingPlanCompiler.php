<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler;

use Generator;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\Exception\CatchPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\CatchPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class PropertyExceptionMappingPlanCompiler
{
    public function __construct(
        /** @var MatchConditionCompiler<Throwable> */
        private readonly MatchConditionCompiler $matchConditionCompiler,
        private readonly bool $failFast = true,
    ) {
    }

    public function getPropertyPlan(ReflectionProperty $reflectionProperty, ObjectExceptionMappingPlanRegistry $planRegistry): ?PropertyExceptionMappingPlan
    {
        $catchPlans = new ReusableIteratorAggregate($this->compileCatchPlans($reflectionProperty));

        $propertyMappingPlan = new PropertyExceptionMappingPlan($reflectionProperty, $catchPlans, $planRegistry);

        if (!$propertyMappingPlan->hasCatchPlans()) {
            $type = new PropertyTypeAnalyser($reflectionProperty->getType());

            if (!$type->allowsMatchableObjects($planRegistry)) {
                return null;
            }
        }

        return $propertyMappingPlan;
    }

    /** @return Generator<int,CatchPlan<Throwable>> */
    private function compileCatchPlans(ReflectionProperty $property): Generator
    {
        foreach ($this->getCatchAttributes($property) as $catch) {
            try {
                $conditionBlueprint = $this->matchConditionCompiler->compile($catch);

                Assert::notNull($conditionBlueprint);

                yield new CatchPlan($conditionBlueprint, $catch->getFormat(), $catch->getMessage());
            } catch (\Throwable $e) {
                if (!$this->failFast) {
                    // One broken #[Catch_] won't spoil the whole match tree.
                    continue;
                }

                throw new CatchPlanCompilationFailedException($property, $e);
            }
        }
    }

    /** @return Generator<Catch_<Throwable,Throwable>> */
    private function getCatchAttributes(ReflectionProperty $property): Generator
    {
        $catchAttributes = $property->getAttributes(Catch_::class);

        foreach ($catchAttributes as $catchAttribute) {
            try {
                yield $catchAttribute->newInstance();
            } catch (Throwable $e) {
                if (!$this->failFast) {
                    // One broken #[Catch_] won't spoil the whole match tree.
                    continue;
                }

                throw new CatchAttributeInstantiationFailedException($property, $e);
            }
        }
    }
}
