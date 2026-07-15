<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationsEmbeddedExceptionFormatter;

use function class_exists;

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload\ConstantsClassLoader
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssemblerService
 *
 * @internal
 */
final class ConstantsAutoloadingCompilerPassIntegrationTest extends BundleTestCase
{
    private ObjectMatchingRuleSetAssemblerService $assembler;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var ObjectMatchingRuleSetAssemblerService $assembler */
        $assembler = $container->get(MatchingRuleSetAssemblerService::class.'<'.ObjectMatchingRuleSetAssembler::class.'>');
        $this->assembler = $assembler;
    }

    public function testClassesThatDefineConstantsAreAutoloaded(): void
    {
        $message = HandleableMessageStub::create();

        $rule = $this->assembler->assemble(new ObjectMatchingRuleSetAssembler($message));

        self::assertNotNull($rule);
        self::assertTrue(class_exists(ExceptionValueMatchConditionCompiler::class, false));
        self::assertTrue(class_exists(ViolationsEmbeddedExceptionFormatter::class, false));
    }
}
