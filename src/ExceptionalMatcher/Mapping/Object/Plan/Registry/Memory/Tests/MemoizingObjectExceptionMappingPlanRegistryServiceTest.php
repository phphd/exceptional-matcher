<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Memory\Tests;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Memory\MemoizingObjectExceptionMappingPlanRegistry::clear
 *
 * @internal
 */
final class MemoizingObjectExceptionMappingPlanRegistryServiceTest extends BundleTestCase
{
    /** @var ObjectExceptionMappingPlanRegistry<object> */
    private ObjectExceptionMappingPlanRegistry $planRegistry;

    private ResetInterface $servicesResetter;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var ObjectExceptionMappingPlanRegistry<object> $planRegistry */
        $planRegistry = $container->get(ObjectExceptionMappingPlanRegistry::class);
        $this->planRegistry = $planRegistry;

        /** @var ResetInterface $servicesResetter */
        $servicesResetter = $container->get('services_resetter');
        $this->servicesResetter = $servicesResetter;
    }

    /** @return list<CompilerPassInterface> */
    protected static function compilerPasses(): array
    {
        return [new PublicServiceCompilerPass(ObjectExceptionMappingPlanRegistry::class)];
    }

    public function testMemoizedPlansAreDroppedOnServicesReset(): void
    {
        $memoizedPlan = $this->planRegistry->getPlan(HandleableMessageStub::class);

        $this->servicesResetter->reset();

        $freshPlan = $this->planRegistry->getPlan(HandleableMessageStub::class);

        self::assertNotSame($memoizedPlan, $freshPlan);
    }
}
