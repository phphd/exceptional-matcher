<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests\Stub\ConditionallyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchConditionFactory
 *
 * @internal
 */
final class ClosureMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
    }

    public function testDoesntCaptureConditionalExceptionWhenConditionIsNotMet(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(12);

        $violationList = $this->matcher->map($message, $originalException);

        self::assertNull($violationList);
    }

    public function testCaptureConditionalException(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(41);

        $matchedExceptionList = $this->matcher->map($message, $originalException);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('nestedObject.conditionalMessage.secondProperty', $matchedException->getRule()->getPropertyPath()->join('.'));
        self::assertSame(41, $matchedException->getRule()->getValue());
    }
}
