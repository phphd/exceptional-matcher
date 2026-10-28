<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\Tests\Trait\AssertCompilationFailedFor;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests\Stub\Invalid\NonValueExceptionClassMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests\Stub\MessageWithExceptionValueCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\QualifiedMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\ExceptionOriginMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\SimpleIfClosureMatchConditionCompiler
 *
 * @internal
 */
final class ExceptionValueMatchConditionUnitTest extends TestCase
{
    use CatchException;
    use AssertCompilationFailedFor;

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

    /**
     * @covers \PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompilationFailedException::getRootException
     * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\PropertyExceptionMappingCompiler
     * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException
     */
    public function testThrowsWhenMatchConditionUsesNonValueExceptionClass(): void
    {
        $message = new NonValueExceptionClassMessage();
        $exception = new RuntimeException();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ExceptionValueMatchConditionCompiler::TYPE_GUARANTEE_MESSAGE);

        $compilationException = self::catchException(
            fn () => $this->matcher->match($exception, $message),
            ObjectExceptionMappingPlanCompilationFailedException::class,
        );

        self::assertCompilationFailedFor($compilationException, NonValueExceptionClassMessage::class, 'value');

        throw $compilationException->getRootException();
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

        self::assertSame('matchedProperty', $matchedException1->getCatchNode()->getPropertyPath()->join('.'));
        self::assertSame('anotherMatchedAsNoCondition', $matchedException2->getCatchNode()->getPropertyPath()->join('.'));
    }
}
