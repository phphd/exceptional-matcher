<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Autoload;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\ViolationsEmbeddedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;

use function class_exists;

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Autoload\ConstantsClassLoader
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry
 *
 * @internal
 */
final class ConstantsAutoloadingCompilerPassIntegrationTest extends BundleTestCase
{
    private ObjectExceptionMappingPlanRegistry $planRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var ObjectExceptionMappingPlanRegistry $planRegistry */
        $planRegistry = $container->get(ObjectExceptionMappingPlanRegistry::class);
        $this->planRegistry = $planRegistry;
    }

    public function testClassesThatDefineConstantsAreAutoloaded(): void
    {
        $plan = $this->planRegistry->getPlan(HandleableMessageStub::class);

        self::assertNotNull($plan);
        self::assertTrue(class_exists(ExceptionValueMatchConditionCompiler::class, false));
        self::assertTrue(class_exists(ViolationsEmbeddedExceptionFormatter::class, false));
    }
}
