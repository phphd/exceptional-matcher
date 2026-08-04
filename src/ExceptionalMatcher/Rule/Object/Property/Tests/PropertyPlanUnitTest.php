<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\CatchExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\CountingMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\InMemoryCompilerRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\InMemoryFormatterRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\LazilyBrokenCatchMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\MultiCatchMessage;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

use function count;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler
 */
final class PropertyPlanUnitTest extends TestCase
{
    public function testCompilesCatchPlansLazilyAndMemoizesThem(): void
    {
        $compiler = new CountingMatchConditionCompiler(
            new CompositeMatchConditionCompiler([new ExceptionClassMatchConditionCompiler()]),
        );
        $registry = new ObjectExceptionMappingPlanRegistry(new ObjectExceptionMappingPlanCompiler(new PropertyExceptionMappingPlanCompiler(new CatchExceptionMappingPlanCompiler($compiler, new InMemoryFormatterRegistry()))), null);

        $plan = $registry->getPlan(MultiCatchMessage::class);

        Assert::notNull($plan);

        $compilations = [];
        $compilations['skeletal plan'] = $compiler->getCompilations();

        [$propertyPlan] = [...$plan->getPropertyPlans()];
        $compilations['property plans traversed'] = $compiler->getCompilations();

        $catchPlans = [...$propertyPlan->getCatchPlans()];
        $compilations['catch plans traversed'] = $compiler->getCompilations();

        self::assertCount(1, [...$plan->getPropertyPlans()]);
        self::assertCount(3, [...$propertyPlan->getCatchPlans()]);
        $compilations['everything re-traversed'] = $compiler->getCompilations();

        self::assertCount(3, $catchPlans);
        self::assertSame([
            // The emptiness check reads the first property plan,
            // which in turn, checks first catch plan, and one plan ahead of the caching iterator
            'skeletal plan' => 2,
            'property plans traversed' => 2,
            'catch plans traversed' => 3,
            'everything re-traversed' => 3,
        ], $compilations);
    }

    public function testFailedCompilationIsRetriedOnNextIteration(): void
    {
        $plan = $this->getLazilyBrokenCatchPlan();

        // the property with a broken catch mapping is still planned - the failure surfaces on access
        [$propertyPlan] = [...$plan->getPropertyPlans()];

        try {
            $catchPlans = [...$propertyPlan->getCatchPlans()];

            self::fail('The broken catch mapping must have failed the compilation, got '.count($catchPlans).' catch plans.');
        } catch (CatchExceptionMappingPlanCompilationFailedException $exception) {
            $previous = $exception->getPrevious();

            self::assertNotNull($previous);
            self::assertStringContainsString('EnumValueMatchCondition requires `from:`', $previous->getMessage());
        }

        $this->expectException(CatchExceptionMappingPlanCompilationFailedException::class);
        $this->expectExceptionMessage('#[Catch_] attribute compilation has failed.');

        self::assertCount(0, [...$propertyPlan->getCatchPlans()]);
    }

    private function getLazilyBrokenCatchPlan(): ObjectExceptionMappingPlan
    {
        /** @psalm-suppress InvalidArgument the compiler registry template is inferred from both key and value positions */
        $compiler = new CompositeMatchConditionCompiler([
            new ExceptionClassMatchConditionCompiler(),
            new DelegatingMatchConditionCompiler(new InMemoryCompilerRegistry([
                EnumValueMatchCondition::class => new EnumValueMatchConditionCompiler(),
            ])),
        ]);

        $registry = new ObjectExceptionMappingPlanRegistry(new ObjectExceptionMappingPlanCompiler(new PropertyExceptionMappingPlanCompiler(new CatchExceptionMappingPlanCompiler($compiler, new InMemoryFormatterRegistry()))), null);

        $plan = $registry->getPlan(LazilyBrokenCatchMessage::class);

        Assert::notNull($plan);

        return $plan;
    }
}
