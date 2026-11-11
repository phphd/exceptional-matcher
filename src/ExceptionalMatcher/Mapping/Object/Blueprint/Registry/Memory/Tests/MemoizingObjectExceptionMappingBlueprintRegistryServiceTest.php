<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Memory\Tests;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Memory\MemoizingObjectExceptionMappingBlueprintRegistry::clear
 *
 * @internal
 */
final class MemoizingObjectExceptionMappingBlueprintRegistryServiceTest extends BundleTestCase
{
    /** @var ObjectExceptionMappingBlueprintRegistry<object> */
    private ObjectExceptionMappingBlueprintRegistry $printRegistry;

    private ResetInterface $servicesResetter;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var ObjectExceptionMappingBlueprintRegistry<object> $printRegistry */
        $printRegistry = $container->get(ObjectExceptionMappingBlueprintRegistry::class);
        $this->printRegistry = $printRegistry;

        /** @var ResetInterface $servicesResetter */
        $servicesResetter = $container->get('services_resetter');
        $this->servicesResetter = $servicesResetter;
    }

    /** @return list<CompilerPassInterface> */
    protected static function compilerPasses(): array
    {
        return [new PublicServiceCompilerPass(ObjectExceptionMappingBlueprintRegistry::class)];
    }

    public function testMemoizedBlueprintsAreDroppedOnServicesReset(): void
    {
        $memoizedPrint = $this->printRegistry->getMappingBlueprint(HandleableMessageStub::class);

        $this->servicesResetter->reset();

        $freshPrint = $this->printRegistry->getMappingBlueprint(HandleableMessageStub::class);

        self::assertNotSame($memoizedPrint, $freshPrint);
    }
}
