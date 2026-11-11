<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Tests\Unit;

use ArrayObject;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Main\Tests\Stub\ObjectPropertyMatchedException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\NestedItemMatchedException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\StaticPropertyMatchedException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\MessageWithNoTryAttribute;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\NestedItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Try_
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 * @covers \PhPhD\ExceptionMapper\Port\MainExceptionMapper
 * @covers \PhPhD\ExceptionMapper\Port\Symfony\Validator\ExceptionToViolationListMapper
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\ObjectExceptionMappingNode
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable\ItemOfIterableExceptionMappingNode
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\PropertyExceptionMappingNode
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Path\PropertyPath
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\ObjectExceptionMappingCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\CompilingObjectExceptionMappingBlueprintRegistry
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Matcher\ExceptionMatcherAggregateAdapter
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\CatchAttributesExceptionMatcherAggregate
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\CatchExceptionMappingNode
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Class\ExceptionClassMatchCondition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Class\ExceptionClassMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\PreCompiledMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\QualifiedMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchCondition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate
 *
 * @internal
 */
final class ExceptionMapperUnitTest extends TestCase
{
    /** @var ExceptionMapper<ConstraintViolationListInterface> */
    private ExceptionMapper $exceptionMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension(nativeProxiesAllowed: false))->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
            'phd_exception_mapper.translation_domain' => 'domain',
        ]);

        $translator = $this->createMock(TranslatorInterface::class);
        $translations = [
            'domain' => [
                'nested.message' => 'nested.message - translated',
            ],
        ];
        $translator->method('trans')
            ->willReturnCallback(static fn(string $id, array $params, string $domain): string => $translations[$domain][$id] ?? $id)
        ;
        $container->set('translator', $translator);

        $container
            ->register(CompositeExceptionUnwrapper::class, CompositeExceptionUnwrapper::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService('phd_exception_toolkit.exception_unwrapper.stack')
        ;

        $container->compile();

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->exceptionMapper = $mapper;
    }

    public function testExceptionIsNotCapturedForMessageWithoutExceptionalValidationAttribute(): void
    {
        $message = new MessageWithNoTryAttribute(123);
        $exception = new AnException();

        $violationList = $this->exceptionMapper->map($exception, $message);

        self::assertNull($violationList);
    }

    public function testCaptureExceptionMappedToProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new AnException();

        $violationList = $this->exceptionMapper->map($originalException, $message);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('property', $violation->getPropertyPath());
    }

    public function testCaptureExceptionMappedToStaticProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new StaticPropertyMatchedException();

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMapper->map($originalException, $message);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame('staticProperty', $violation->getPropertyPath());
        self::assertSame('foo', $violation->getInvalidValue());
    }

    /** @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\MixedObjectType */
    public function testNestedObjectIsNotCapturedWhenPropertyIsNotInitialized(): void
    {
        $message = HandleableMessageStub::create();
        $exception = new NestedPropertyMatchedException();

        $violationList = $this->exceptionMapper->map($exception, $message);

        self::assertNull($violationList);
    }

    /** @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Type\PropertyType::compoundTypeIncludes */
    public function testCaptureNestedObjectPropertyException(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());
        $originalException = new NestedPropertyMatchedException();

        $violationList = $this->exceptionMapper->map($originalException, $message);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];

        self::assertSame('nested.message - translated', $violation->getMessage());
        self::assertSame('nested.message', $violation->getMessageTemplate());
        self::assertSame('nestedObject.nestedProperty', $violation->getPropertyPath());
        self::assertNull($violation->getInvalidValue());
    }

    public function testUncaughtExceptionsAreNotAllowed(): void
    {
        $message = HandleableMessageStub::create()
            ->withNestedArrayItems([
                'first' => new NestedItem(1),
                'second' => new NestedItem(2),
            ])
        ;

        $exceptionAdapter = new CompositeException([
            new NestedItemMatchedException(code: 1),
            new NestedItemMatchedException(code: 3), // not caught
        ]);

        $violationList = $this->exceptionMapper->map($exceptionAdapter, $message);

        self::assertNull($violationList);
    }

    public function testCaptureMultipleExceptions(): void
    {
        $message = HandleableMessageStub::create()
            ->withNestedArrayItems([
                'first' => new NestedItem(2),
            ])
            ->withNestedIterableItems(new ArrayObject([
                'second' => new NestedItem(1),
            ]))
        ;

        $exceptionAdapter = new CompositeException([
            new NestedItemMatchedException(code: 1),
            new AnException(),
            new ObjectPropertyMatchedException(),
            new NestedItemMatchedException(code: 2),
        ]);

        $violationList = $this->exceptionMapper->map($exceptionAdapter, $message);

        self::assertNotNull($violationList);
        self::assertCount(4, $violationList);

        /** @var ConstraintViolationInterface $firstViolation */
        $firstViolation = $violationList[0];
        self::assertSame('property', $firstViolation->getPropertyPath());

        /** @var ConstraintViolationInterface $secondViolation */
        $secondViolation = $violationList[1];
        self::assertSame('objectProperty', $secondViolation->getPropertyPath());

        /** @var ConstraintViolationInterface $thirdViolation */
        $thirdViolation = $violationList[2];
        self::assertSame('nestedArrayItems[first].property', $thirdViolation->getPropertyPath());

        /** @var ConstraintViolationInterface $fourthViolation */
        $fourthViolation = $violationList[3];
        self::assertSame('nestedIterableItems[second].property', $fourthViolation->getPropertyPath());
    }
}
