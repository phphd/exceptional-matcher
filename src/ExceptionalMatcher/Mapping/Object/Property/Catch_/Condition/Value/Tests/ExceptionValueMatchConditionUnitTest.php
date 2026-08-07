<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\Tests\Stub\MessageWithExceptionValueCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\ExceptionValueMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\ExceptionValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Delegating\DelegatingMatchConditionCompiler
 *
 * @internal
 */
final class ExceptionValueMatchConditionUnitTest extends TestCase
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

        $container
            ->register(CompositeExceptionUnwrapper::class, CompositeExceptionUnwrapper::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService('phd_exception_toolkit.exception_unwrapper.stack')
        ;

        $container->compile();

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
    }

    public function testValueExceptionCondition(): void
    {
        $message = new MessageWithExceptionValueCondition();

        $exceptionAdapter = new CompositeException([
            new SomeValueException('matched!'),
            new SomeValueException('whatever'),
        ]);

        $matchedExceptionList = $this->matcher->match($exceptionAdapter, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(2, $matchedExceptionList);
        [$matchedException1, $matchedException2] = $matchedExceptionList->toArray();

        self::assertSame('matchedProperty', $matchedException1->getRule()->getPropertyPath()->join('.'));
        self::assertSame('anotherMatchedAsNoCondition', $matchedException2->getRule()->getPropertyPath()->join('.'));
    }
}
