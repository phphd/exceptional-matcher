<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler;
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
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\RestartableIteratorAggregate
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\PropertyExceptionMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\CatchPlan
 */
final class ClassMatchingPlanUnitTest extends TestCase
{
    private ObjectExceptionMappingPlanRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $compiler = new CompositeMatchConditionCompiler([
            new ExceptionClassMatchConditionCompiler(),
        ]);

        $this->registry = new ObjectExceptionMappingPlanRegistry(new ObjectExceptionMappingPlanCompiler(new PropertyExceptionMappingPlanCompiler($compiler)), null);
    }

    public function testDiscardsPropertiesThatCanNeverMatch(): void
    {
        $plan = $this->registry->getPlan(TypedPropertiesMessage::class);

        Assert::notNull($plan);

        $propertyNames = array_map(
            static fn (PropertyExceptionMappingPlan $propertyPlan): string => $propertyPlan->getName(),
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

        self::assertTrue($this->getPlanFor($message)->bind($message)->match($reciprocal));

        [$matchedException] = $reciprocal->getMatchedExceptionList()->toArray();

        self::assertSame($exception, $matchedException->getException());
        self::assertSame('caughtValue', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testBindsNestedObjectRules(): void
    {
        $message = BindableMessage::create()->withNestedItem(new PlannedItem('kernel'));
        $exception = new NestedStubException('nested oops');

        $reciprocal = new ExceptionReciprocal([$exception]);

        self::assertTrue($this->getPlanFor($message)->bind($message)->match($reciprocal));

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

        self::assertTrue($this->getPlanFor($message)->bind($message)->match($reciprocal));

        [$matchedException] = $reciprocal->getMatchedExceptionList()->toArray();

        self::assertSame('listItems[first].itemValue', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testDoesNotMatchUnmappedException(): void
    {
        $message = BindableMessage::create();

        $reciprocal = new ExceptionReciprocal([new NestedStubException('unmatched')]);

        self::assertFalse($this->getPlanFor($message)->bind($message)->match($reciprocal));
    }

    private function getPlanFor(BindableMessage $message): ObjectExceptionMappingPlan
    {
        $plan = $this->registry->getPlan($message::class);

        Assert::notNull($plan);

        return $plan;
    }
}
