<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub\ConditionallyCaughtException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub\ConditionalMessageHolder;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\ClosureMatchCondition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\SimpleIfClosureMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\SimpleIfClosureMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 *
 * @internal
 */
final class ClosureMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<MatchedExceptionList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<MatchedExceptionList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.MatchedExceptionList::class.'>');
        $this->mapper = $mapper;
    }

    public function testDoesntCaptureConditionalExceptionWhenConditionIsNotMet(): void
    {
        $message = new ConditionalMessageHolder(ConditionalMessage::createWithConditionalProperties(11, 41));
        $originalException = new ConditionallyCaughtException(12);

        $violationList = $this->mapper->map($originalException, $message);

        self::assertNull($violationList);
    }

    public function testCaptureConditionalException(): void
    {
        $message = new ConditionalMessageHolder(ConditionalMessage::createWithConditionalProperties(11, 41));
        $originalException = new ConditionallyCaughtException(41);

        $matchedExceptionList = $this->mapper->map($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$exceptionMatch] = $matchedExceptionList->toArray();

        self::assertSame('conditionalMessage.secondProperty', $exceptionMatch->getNode()->getPropertyPath()->join('.'));
        self::assertSame(41, $exceptionMatch->getNode()->getValue());
    }
}
