<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\ViolationsEmbeddedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;

use function class_exists;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\CompilingObjectExceptionMappingPlanRegistry
 *
 * @internal
 */
final class ConstantsAutoloadingCompilerPassIntegrationTest extends BundleTestCase
{
    /** @var ObjectExceptionMappingPlanRegistry<object> */
    private ObjectExceptionMappingPlanRegistry $planRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var ObjectExceptionMappingPlanRegistry<object> $planRegistry */
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
