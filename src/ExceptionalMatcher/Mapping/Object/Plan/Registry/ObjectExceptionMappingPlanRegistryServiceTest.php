<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Memory\MemoizingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\MessageWithNoTryAttribute;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @coversNothing
 *
 * @internal
 */
final class ObjectExceptionMappingPlanRegistryServiceTest extends BundleTestCase
{
    /** @return list<CompilerPassInterface> */
    protected static function compilerPasses(): array
    {
        return [new PublicServiceCompilerPass(ObjectExceptionMappingPlanRegistry::class)];
    }

    public function testClassMatchingPlanRegistryService(): void
    {
        $planRegistry = self::getContainer()->get(ObjectExceptionMappingPlanRegistry::class);

        self::assertInstanceOf(MemoizingObjectExceptionMappingPlanRegistry::class, $planRegistry);

        self::assertNull($planRegistry->getPlan(MessageWithNoTryAttribute::class));
        self::assertNotNull($planRegistry->getPlan(HandleableMessageStub::class));
    }

    public function testMemoizedPlansAreDroppedOnServicesReset(): void
    {
        $container = self::getContainer();

        /** @var ObjectExceptionMappingPlanRegistry<object> $planRegistry */
        $planRegistry = $container->get(ObjectExceptionMappingPlanRegistry::class);

        $plan = $planRegistry->getPlan(HandleableMessageStub::class);

        self::assertSame($plan, $planRegistry->getPlan(HandleableMessageStub::class));

        /** @var ResetInterface $servicesResetter */
        $servicesResetter = $container->get('services_resetter');
        $servicesResetter->reset();

        self::assertNotSame($plan, $planRegistry->getPlan(HandleableMessageStub::class));
    }
}
