<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests\Stub\ConditionallyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchConditionFactory
 *
 * @internal
 */
final class ClosureMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<non-empty-list<PropriatedException<Throwable>>> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<non-empty-list<PropriatedException<Throwable>>> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<non-empty-list<'.PropriatedException::class.'<Throwable>>>');
        $this->mapper = $mapper;
    }

    public function testDoesntCaptureConditionalExceptionWhenConditionIsNotMet(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(12);

        $violationList = $this->mapper->map($message, $originalException);

        self::assertNull($violationList);
    }

    public function testCaptureConditionalException(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(41);

        $propriatedExceptionList = $this->mapper->map($message, $originalException);

        self::assertNotNull($propriatedExceptionList);
        self::assertCount(1, $propriatedExceptionList);

        $propriatedException = $propriatedExceptionList[0];
        self::assertSame('nestedObject.conditionalMessage.secondProperty', $propriatedException->getMatchedRule()->getPropertyPath()->join('.'));
        self::assertSame(41, $propriatedException->getMatchedRule()->getValue());
    }
}
