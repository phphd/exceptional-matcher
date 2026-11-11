<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Memory\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Memory\MemoizingObjectExceptionMappingBlueprintRegistry
 *
 * @internal
 */
final class MemoizingObjectExceptionMappingBlueprintRegistryUnitTest extends TestCase
{
    /** @var ObjectExceptionMappingBlueprintRegistry<object> */
    private ObjectExceptionMappingBlueprintRegistry $printRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ObjectExceptionMappingBlueprintRegistry::class));

        $container->compile();

        /** @var ObjectExceptionMappingBlueprintRegistry<object> $printRegistry */
        $printRegistry = $container->get(ObjectExceptionMappingBlueprintRegistry::class);
        $this->printRegistry = $printRegistry;
    }

    public function testBlueprintsAreMemoized(): void
    {
        self::assertSame(
            $this->printRegistry->getMappingBlueprint(HandleableMessageStub::class),
            $this->printRegistry->getMappingBlueprint(HandleableMessageStub::class),
        );
        self::assertInstanceOf(
            ObjectExceptionMappingBlueprint::class,
            $this->printRegistry->getMappingBlueprint(HandleableMessageStub::class),
        );
    }
}
