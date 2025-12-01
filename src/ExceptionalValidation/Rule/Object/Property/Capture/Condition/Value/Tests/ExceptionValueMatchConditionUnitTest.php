<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Throwable;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating\DelegatingMatchConditionFactory
 *
 * @internal
 */
final class ExceptionValueMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<non-empty-list<CapturedException<Throwable>>> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = PhdExceptionalValidationExtension::getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ], true);

        $container
            ->register(CompositeExceptionUnwrapper::class, CompositeExceptionUnwrapper::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService('phd_exception_toolkit.exception_unwrapper.stack')
        ;

        $container->compile();

        /** @var ExceptionMapper<non-empty-list<CapturedException<Throwable>>> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<non-empty-list<'.CapturedException::class.'<Throwable>>>');
        $this->mapper = $mapper;
    }

    public function testValueExceptionCondition(): void
    {
        $message = HandleableMessageStub::create();

        $exceptionAdapter = new CompositeException([
            new SomeValueException('matched!'),
            new SomeValueException('whatever'),
        ]);

        $capturedExceptions = $this->mapper->map($message, $exceptionAdapter);

        self::assertNotNull($capturedExceptions);
        self::assertCount(2, $capturedExceptions);
        [$capturedException1, $capturedException2] = $capturedExceptions;

        self::assertSame('matchedProperty', $capturedException1->getMatchedRule()->getPropertyPath()->join('.'));
        self::assertSame('anotherMatchedAsNoCondition', $capturedException2->getMatchedRule()->getPropertyPath()->join('.'));
    }
}
