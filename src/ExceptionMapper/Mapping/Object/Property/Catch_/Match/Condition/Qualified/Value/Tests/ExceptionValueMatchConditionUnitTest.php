<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests\Stub\MessageWithExceptionValueCondition;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatchList;
use PhPhD\ExceptionMapper\Tests\Trait\CatchException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\ExceptionValueMatchCondition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\QualifiedMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\TraceOrigin\ExceptionTraceOriginMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Closure\SimpleIfClosureMatchConditionCompiler
 *
 * @internal
 */
final class ExceptionValueMatchConditionUnitTest extends TestCase
{
    use CatchException;

    /** @var ExceptionMapper<ExceptionMatchList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container
            ->register(CompositeExceptionUnwrapper::class, CompositeExceptionUnwrapper::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService('phd_exception_toolkit.exception_unwrapper.stack')
        ;

        $container->compile();

        /** @var ExceptionMapper<ExceptionMatchList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ExceptionMatchList::class.'>');
        $this->mapper = $mapper;
    }

    public function testValueExceptionCondition(): void
    {
        $message = new MessageWithExceptionValueCondition();

        $exceptionAdapter = new CompositeException([
            new SomeValueException('matched!'),
            new SomeValueException('whatever'),
        ]);

        $exceptionMatches = $this->mapper->map($exceptionAdapter, $message);

        self::assertNotNull($exceptionMatches);
        self::assertCount(2, $exceptionMatches);
        [$exceptionMatch1, $exceptionMatch2] = $exceptionMatches->toArray();

        self::assertSame('matchedProperty', $exceptionMatch1->getNode()->getPropertyPath()->join('.'));
        self::assertSame('anotherMatchedAsNoCondition', $exceptionMatch2->getNode()->getPropertyPath()->join('.'));
    }
}
