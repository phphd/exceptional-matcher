<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\TestServicesCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\entos\Plan\Registry\MemoizingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\entos\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\TypedPropertiesMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\UnmarkedMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\DecoratorServicePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\entos\Plan\Registry\CompilingObjectExceptionMappingPlanRegistry
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\entos\Plan\Registry\MemoizingObjectExceptionMappingPlanRegistry
 */
final class ClassMatchingPlanRegistryUnitTest extends TestCase
{
    /** @var ObjectExceptionMappingPlanRegistry<object> */
    private ObjectExceptionMappingPlanRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension(true))->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new TestServicesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, TestServicesCompilerPass::PRIORITY);
        $container->addCompilerPass(new DecoratorServicePass(), PassConfig::TYPE_OPTIMIZE);

        $container->compile();

        /** @var ObjectExceptionMappingPlanRegistry<object> $registry */
        $registry = $container->get(ObjectExceptionMappingPlanRegistry::class);
        $this->registry = $registry;
    }

    public function testReturnsNullForClassWithoutTryAttribute(): void
    {
        self::assertNull($this->registry->getPlan(UnmarkedMessage::class));
        self::assertNull($this->registry->getPlan(UnmarkedMessage::class));
    }

    public function testMemoizesPlanPerClass(): void
    {
        self::assertInstanceOf(MemoizingObjectExceptionMappingPlanRegistry::class, $this->registry);

        $plan = $this->registry->getPlan(TypedPropertiesMessage::class);

        self::assertNotNull($plan);
        self::assertSame($plan, $this->registry->getPlan(TypedPropertiesMessage::class));
    }
}
