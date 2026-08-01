<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NotHandleableMessageStub;

/**
 * @coversNothing
 *
 * @internal
 */
final class ObjectExceptionMappingPlanRegistryServiceTest extends BundleTestCase
{
    public function testClassMatchingPlanRegistryService(): void
    {
        $planRegistry = self::getContainer()->get(ObjectExceptionMappingPlanRegistry::class);

        self::assertInstanceOf(ObjectExceptionMappingPlanRegistry::class, $planRegistry);

        self::assertFalse($planRegistry->hasPlan(NotHandleableMessageStub::class));
        self::assertTrue($planRegistry->hasPlan(HandleableMessageStub::class));
    }
}
