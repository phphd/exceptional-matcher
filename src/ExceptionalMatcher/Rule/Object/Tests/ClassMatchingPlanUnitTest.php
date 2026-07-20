<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Compiler\ClassMatchingPlanFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Plan\ClassMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\BindableMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\NestedStubException;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\PlannedItem;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\PlanStubException;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\TypedPropertiesMessage;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

use function array_map;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Plan\ClassMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Compiler\ClassMatchingPlanFactory
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\RestartableIteratorAggregate
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan
 */
final class ClassMatchingPlanUnitTest extends TestCase
{
    private ClassMatchingPlanRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $compiler = new CompositeMatchConditionCompiler([
            new ExceptionClassMatchConditionCompiler(),
        ]);

        $this->registry = new ClassMatchingPlanRegistry(new ClassMatchingPlanFactory($compiler), null);
    }

    public function testDiscardsPropertiesThatCanNeverMatch(): void
    {
        $plan = $this->registry->getPlan(TypedPropertiesMessage::class);

        Assert::notNull($plan);

        $propertyNames = array_map(
            static fn (PropertyMappingPlan $propertyPlan): string => $propertyPlan->getName(),
            [...$plan->getPropertyPlans()],
        );

        self::assertSame([
            'extensibleItem',
            'interfaceItem',
            'unionItem',
            'arrayItems',
            'plannedItem',
            'caughtValue',
        ], $propertyNames);
    }

    public function testBindsCatchRules(): void
    {
        $message = BindableMessage::create();
        $exception = new PlanStubException('oops');

        $reciprocal = new ExceptionReciprocal([$exception]);

        self::assertTrue($this->getPlanFor($message)->bind($message)->process($reciprocal));

        [$matchedException] = $reciprocal->getMatchedExceptionList()->toArray();

        self::assertSame($exception, $matchedException->getException());
        self::assertSame('caughtValue', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testBindsNestedObjectRules(): void
    {
        $message = BindableMessage::create()->withNestedItem(new PlannedItem('kernel'));
        $exception = new NestedStubException('nested oops');

        $reciprocal = new ExceptionReciprocal([$exception]);

        self::assertTrue($this->getPlanFor($message)->bind($message)->process($reciprocal));

        [$matchedException] = $reciprocal->getMatchedExceptionList()->toArray();

        self::assertSame('nestedItem.itemValue', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testBindsIterableItemRulesWithKeyedPaths(): void
    {
        $message = BindableMessage::create()->withListItems([
            'first' => new PlannedItem('kernel'),
        ]);
        $exception = new NestedStubException('nested oops');

        $reciprocal = new ExceptionReciprocal([$exception]);

        self::assertTrue($this->getPlanFor($message)->bind($message)->process($reciprocal));

        [$matchedException] = $reciprocal->getMatchedExceptionList()->toArray();

        self::assertSame('listItems[first].itemValue', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testDoesNotMatchUnmappedException(): void
    {
        $message = BindableMessage::create();

        $reciprocal = new ExceptionReciprocal([new NestedStubException('unmatched')]);

        self::assertFalse($this->getPlanFor($message)->bind($message)->process($reciprocal));
    }

    private function getPlanFor(BindableMessage $message): ClassMappingPlan
    {
        $plan = $this->registry->getPlan($message::class);

        Assert::notNull($plan);

        return $plan;
    }
}
