<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Memory\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Memory\MemoizingObjectExceptionMappingPlanRegistry
 *
 * @internal
 */
final class MemoizingObjectExceptionMappingPlanRegistryUnitTest extends TestCase
{
    /** @var ObjectExceptionMappingPlanRegistry<object> */
    private ObjectExceptionMappingPlanRegistry $planRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ObjectExceptionMappingPlanRegistry::class));

        $container->compile();

        /** @var ObjectExceptionMappingPlanRegistry<object> $planRegistry */
        $planRegistry = $container->get(ObjectExceptionMappingPlanRegistry::class);
        $this->planRegistry = $planRegistry;
    }

    public function testPlansAreMemoized(): void
    {
        self::assertSame(
            $this->planRegistry->getPlan(HandleableMessageStub::class),
            $this->planRegistry->getPlan(HandleableMessageStub::class),
        );
        self::assertInstanceOf(
            ObjectExceptionMappingPlan::class,
            $this->planRegistry->getPlan(HandleableMessageStub::class),
        );
    }
}
