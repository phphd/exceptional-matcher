<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub\ConditionallyCaughtException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub\ConditionalMessageHolder;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\ClosureMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\SimpleIfClosureMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\SimpleIfClosureMatchConditionPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
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

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
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
        $message = new ConditionalMessageHolder(ConditionalMessage::createWithConditionalProperties(11, 41));
        $originalException = new ConditionallyCaughtException(12);

        $violationList = $this->matcher->match($originalException, $message);

        self::assertNull($violationList);
    }

    public function testCaptureConditionalException(): void
    {
        $message = new ConditionalMessageHolder(ConditionalMessage::createWithConditionalProperties(11, 41));
        $originalException = new ConditionallyCaughtException(41);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('conditionalMessage.secondProperty', $matchedException->getRule()->getPropertyPath()->join('.'));
        self::assertSame(41, $matchedException->getRule()->getValue());
    }
}
