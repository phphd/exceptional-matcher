<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Memory\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Memory\MemoizingObjectExceptionMappingBlueprintRegistry
 *
 * @internal
 */
final class MemoizingObjectExceptionMappingPlanRegistryUnitTest extends TestCase
{
    /** @var ObjectExceptionMappingBlueprintRegistry<object> */
    private ObjectExceptionMappingBlueprintRegistry $planRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ObjectExceptionMappingBlueprintRegistry::class));

        $container->compile();

        /** @var ObjectExceptionMappingBlueprintRegistry<object> $planRegistry */
        $planRegistry = $container->get(ObjectExceptionMappingBlueprintRegistry::class);
        $this->planRegistry = $planRegistry;
    }

    public function testPlansAreMemoized(): void
    {
        self::assertSame(
            $this->planRegistry->getMappingBlueprint(HandleableMessageStub::class),
            $this->planRegistry->getMappingBlueprint(HandleableMessageStub::class),
        );
        self::assertInstanceOf(
            ObjectExceptionMappingBlueprint::class,
            $this->planRegistry->getMappingBlueprint(HandleableMessageStub::class),
        );
    }
}
