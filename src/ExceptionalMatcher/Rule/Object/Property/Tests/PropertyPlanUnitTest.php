<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Compiler\ClassMatchingPlanFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Plan\ClassMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\CountingMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\InMemoryCompilerRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\MultiCatchMessage;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

use function count;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Compiler\ClassMatchingPlanFactory
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\RestartableIteratorAggregate
 */
final class PropertyPlanUnitTest extends TestCase
{
    public function testCompilesCatchPlansLazilyAndMemoizesThem(): void
    {
        $compiler = new CountingMatchConditionCompiler(
            new CompositeMatchConditionCompiler([new ExceptionClassMatchConditionCompiler()]),
        );
        $registry = new ClassMatchingPlanRegistry(new ClassMatchingPlanFactory($compiler), null);

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
            'skeletal plan' => 0,
            // the emptiness check reads one catch plan ahead of the caching iterator
            'property plans traversed' => 2,
            'catch plans traversed' => 3,
            'everything re-traversed' => 3,
        ], $compilations);
    }

    public function testFailedCompilationIsRetriedOnNextIteration(): void
    {
        $plan = $this->getEnumStubMessagePlan();

        // the property with a broken catch mapping is still planned - the failure surfaces on access
        [$propertyPlan] = [...$plan->getPropertyPlans()];

        try {
            $catchPlans = [...$propertyPlan->getCatchPlans()];

            self::fail('The broken catch mapping must have failed the compilation, got '.count($catchPlans).' catch plans.');
        } catch (LogicException $exception) {
            self::assertStringContainsString('EnumValueMatchCondition requires `from:`', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition requires `from:`');

        self::assertCount(0, [...$propertyPlan->getCatchPlans()]);
    }

    private function getEnumStubMessagePlan(): ClassMappingPlan
    {
        /** @psalm-suppress InvalidArgument the compiler registry template is inferred from both key and value positions */
        $compiler = new CompositeMatchConditionCompiler([
            new ExceptionClassMatchConditionCompiler(),
            new DelegatingMatchConditionCompiler(new InMemoryCompilerRegistry([
                EnumValueMatchCondition::class => new EnumValueMatchConditionCompiler(),
            ])),
        ]);

        $registry = new ClassMatchingPlanRegistry(new ClassMatchingPlanFactory($compiler), null);

        $plan = $registry->getPlan(MissingEnumFromConditionMessage::class);

        Assert::notNull($plan);

        return $plan;
    }
}
