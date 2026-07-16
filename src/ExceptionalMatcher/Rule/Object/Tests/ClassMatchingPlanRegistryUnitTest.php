<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests;

use ArrayObject;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\TypedPropertiesMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\UnmarkedMessage;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry
 */
final class ClassMatchingPlanRegistryUnitTest extends TestCase
{
    public function testReturnsNullForClassWithoutTryAttribute(): void
    {
        $registry = $this->createRegistry();

        self::assertNull($registry->getPlan(UnmarkedMessage::class));
        self::assertNull($registry->getPlan(UnmarkedMessage::class));
    }

    public function testMemoizesPlanPerClass(): void
    {
        $registry = $this->createRegistry();

        $plan = $registry->getPlan(TypedPropertiesMessage::class);

        self::assertNotNull($plan);
        self::assertSame($plan, $registry->getPlan(TypedPropertiesMessage::class));
    }

    public function testAutoloadsClassNamesOnceBeforeFirstPlan(): void
    {
        $autoloadCalls = new ArrayObject();

        $registry = $this->createRegistry(static function () use ($autoloadCalls): void {
            $autoloadCalls->append(true);
        });

        self::assertCount(0, $autoloadCalls);

        $registry->getPlan(UnmarkedMessage::class);
        $registry->getPlan(TypedPropertiesMessage::class);

        self::assertCount(1, $autoloadCalls);
    }

    private function createRegistry(?callable $autoloadClassNames = null): ClassMatchingPlanRegistry
    {
        $compiler = new CompositeMatchConditionCompiler([
            new ExceptionClassMatchConditionCompiler(),
        ]);

        return new ClassMatchingPlanRegistry(
            new ClassMatchingPlanFactory($compiler),
            null !== $autoloadClassNames ? $autoloadClassNames(...) : null,
        );
    }
}
