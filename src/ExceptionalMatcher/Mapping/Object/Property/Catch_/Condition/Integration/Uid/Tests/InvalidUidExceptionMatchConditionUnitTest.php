<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Uid\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\Tests\Trait\CompilationExceptionRoot;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Uid\InvalidUidExceptionMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Uid\Tests\Stub\Invalid\NonInvalidUidExceptionClassMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Uid\Tests\Stub\MessageWithInvalidUidCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Symfony\Component\Uid\Uuid;

use function class_exists;
use function property_exists;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Uid\InvalidUidExceptionMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Uid\InvalidUidExceptionMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
 *
 * @internal
 */
final class InvalidUidExceptionMatchConditionUnitTest extends TestCase
{
    use CatchException;
    use CompilationExceptionRoot;

    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(InvalidUidException::class) || !property_exists(InvalidUidException::class, 'invalidValue')) {
            self::markTestSkipped('Installed version of Symfony Uid component does not fit for matching.');
        }

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
    }

    public function testThrowsWhenMatchConditionUsesNonInvalidUidExceptionClass(): void
    {
        $message = new NonInvalidUidExceptionClassMessage();
        $exception = new RuntimeException();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(InvalidUidExceptionMatchConditionCompiler::TYPE_GUARANTEE_MESSAGE);

        $compilationException = self::catchException(
            fn () => $this->matcher->match($exception, $message),
            ObjectExceptionMappingPlanCompilationFailedException::class,
        );

        throw self::compilationExceptionRoot($compilationException, NonInvalidUidExceptionClassMessage::class, 'uid');
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsNull(): void
    {
        $message = new MessageWithInvalidUidCondition(uid: null);

        $originalException = self::catchException(
            static fn (): Uuid => Uuid::fromString('just bad'),
            InvalidUidException::class,
        );

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsNotCapturedSinceWhenValueIsNotStringable(): void
    {
        $message = new MessageWithInvalidUidCondition([
            'a' => 'very bad',
        ]);

        $originalException = self::catchException(
            static fn (): Uuid => Uuid::fromString('very bad'),
            InvalidUidException::class,
        );

        $this->expectExceptionMessage('InvalidUidExceptionMatchCondition requires a stringable value, got: array');
        $this->expectException(InvalidArgumentException::class);

        $this->matcher->match($originalException, $message);
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsDifferent(): void
    {
        $message = new MessageWithInvalidUidCondition('way too bad');

        $originalException = self::catchException(
            static fn (): Uuid => Uuid::fromString('just bad'),
            InvalidUidException::class,
        );

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsCapturedWhenValueMatches(): void
    {
        $message = new MessageWithInvalidUidCondition('bad');

        $originalException = self::catchException(
            static fn (): Uuid => Uuid::fromString('bad'),
            InvalidUidException::class,
        );

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);
        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame($originalException, $matchedException->getException());
        self::assertSame('uid', $matchedException->getCatchNode()->getPropertyPath()->join('.'));
    }
}
