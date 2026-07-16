<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NotHandleableMessageStub;

/**
 * @coversNothing
 *
 * @internal
 */
final class ClassMatchingPlanRegistryServiceTest extends BundleTestCase
{
    public function testClassMatchingPlanRegistryService(): void
    {
        $planRegistry = self::getContainer()->get(ClassMatchingPlanRegistry::class);

        self::assertInstanceOf(ClassMatchingPlanRegistry::class, $planRegistry);

        self::assertNull($planRegistry->getPlan(NotHandleableMessageStub::class));
        self::assertNotNull($planRegistry->getPlan(HandleableMessageStub::class));
    }
}
