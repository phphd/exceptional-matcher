<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\Tests\Stub\MessageWithInvalidUidCondition;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatchList;
use PhPhD\ExceptionMapper\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Symfony\Component\Uid\Uuid;

use function class_exists;
use function property_exists;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\UidValueMatchCondition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\UidValueMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\UidValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 *
 * @internal
 */
final class UidValueMatchConditionUnitTest extends TestCase
{
    use CatchException;

    /** @var ExceptionMapper<ExceptionMatchList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(InvalidUidException::class) || !property_exists(InvalidUidException::class, 'invalidValue')) {
            self::markTestSkipped('Installed version of Symfony Uid component does not fit for matching.');
        }

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<ExceptionMatchList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ExceptionMatchList::class.'>');
        $this->mapper = $mapper;
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsNull(): void
    {
        $message = new MessageWithInvalidUidCondition(id: null);

        $originalException = self::catchException(
            static fn(): Uuid => Uuid::fromString('just bad'),
            InvalidUidException::class,
        );

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsNotString(): void
    {
        $message = new MessageWithInvalidUidCondition(1189);

        $originalException = self::catchException(
            static fn(): Uuid => Uuid::fromString('1189'),
            InvalidUidException::class,
        );

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsDifferent(): void
    {
        $message = new MessageWithInvalidUidCondition('way too bad');

        $originalException = self::catchException(
            static fn(): Uuid => Uuid::fromString('just bad'),
            InvalidUidException::class,
        );

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    public function testInvalidUidExceptionIsCapturedWhenValueMatches(): void
    {
        $message = new MessageWithInvalidUidCondition('bad');

        $originalException = self::catchException(
            static fn(): Uuid => Uuid::fromString('bad'),
            InvalidUidException::class,
        );

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNotNull($exceptionMatches);
        self::assertCount(1, $exceptionMatches);
        [$exceptionMatch] = $exceptionMatches->toArray();

        self::assertSame($originalException, $exceptionMatch->getException());
        self::assertSame('id', $exceptionMatch->getNode()->getPropertyPath()->join('.'));
    }
}
