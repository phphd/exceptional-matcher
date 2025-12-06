<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating\DelegatingMatchConditionFactory
 *
 * @internal
 */
final class ExceptionValueMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<MatchedExceptionList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container
            ->register(CompositeExceptionUnwrapper::class, CompositeExceptionUnwrapper::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService('phd_exception_toolkit.exception_unwrapper.stack')
        ;

        $container->compile();

        /** @var ExceptionMapper<MatchedExceptionList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.MatchedExceptionList::class.'>');
        $this->mapper = $mapper;
    }

    public function testValueExceptionCondition(): void
    {
        $message = HandleableMessageStub::create();

        $exceptionAdapter = new CompositeException([
            new SomeValueException('matched!'),
            new SomeValueException('whatever'),
        ]);

        $matchedExceptionList = $this->mapper->map($message, $exceptionAdapter);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(2, $matchedExceptionList);
        [$matchedException1, $matchedException2] = $matchedExceptionList->toArray();

        self::assertSame('matchedProperty', $matchedException1->getRule()->getPropertyPath()->join('.'));
        self::assertSame('anotherMatchedAsNoCondition', $matchedException2->getRule()->getPropertyPath()->join('.'));
    }
}
