<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Autoload;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\ViolationsEmbeddedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;

use function class_exists;

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Autoload\ConstantsClassLoader
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry
 *
 * @internal
 */
final class ConstantsAutoloadingCompilerPassIntegrationTest extends BundleTestCase
{
    private ClassMatchingPlanRegistry $planRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var ClassMatchingPlanRegistry $planRegistry */
        $planRegistry = $container->get(ClassMatchingPlanRegistry::class);
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
