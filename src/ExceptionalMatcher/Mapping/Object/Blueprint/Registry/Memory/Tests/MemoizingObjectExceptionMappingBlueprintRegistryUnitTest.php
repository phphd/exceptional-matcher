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
final class MemoizingObjectExceptionMappingBlueprintRegistryUnitTest extends TestCase
{
    /** @var ObjectExceptionMappingBlueprintRegistry<object> */
    private ObjectExceptionMappingBlueprintRegistry $printRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
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
