<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\Tests;

use LogicException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\Tests\Stub\MessageContainingException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\Tests\Stub\ObjectPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\DefaultExceptionViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet
 *
 * @internal
 */
final class DefaultExceptionViolationFormatterUnitTest extends TestCase
{
    /** @var ExceptionMapper<ConstraintViolationListInterface> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = PhdExceptionalValidationExtension::getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ], true);

        $translator = $this->createMock(TranslatorInterface::class);
        $translations = [
            'domain' => [
                'oops' => 'oops - translated',
            ],
        ];
        $translator->method('trans')
            ->willReturnCallback(static fn (string $id, array $params, string $domain): string => $translations[$domain][$id] ?? $id)
        ;
        $container->set('translator', $translator);
        $container->setParameter('validator.translation_domain', 'domain');

        $container
            ->register(CompositeExceptionUnwrapper::class, CompositeExceptionUnwrapper::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService('phd_exception_toolkit.exception_unwrapper.stack')
        ;

        $container->compile();

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->mapper = $mapper;
    }

    public function testFormatException(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new PropertyCapturableException();

        $violationList = $this->mapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('property', $violation->getPropertyPath());
        self::assertSame('oops - translated', $violation->getMessage());
        self::assertSame('oops', $violation->getMessageTemplate());
        self::assertSame([], $violation->getParameters());
        self::assertSame($message, $violation->getRoot());
        self::assertNull($violation->getInvalidValue());
    }

    public function testPropertyInvalidValueIsCollected(): void
    {
        $message = HandleableMessageStub::create()->withMessageText('invalid text value');

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->mapper->map($message, new LogicException());

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame('invalid text value', $violation->getInvalidValue());
    }

    public function testObjectInvalidValueIsCollected(): void
    {
        $message = HandleableMessageStub::create()->withObjectProperty($object = new stdClass());

        $originalException = new ObjectPropertyCapturableException();

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->mapper->map($message, $originalException);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame($object, $violation->getInvalidValue());
    }

    public function testViolationMessageFallsBackToExceptionMessage(): void
    {
        $message = HandleableMessageStub::create();
        $exceptionAdapter = new CompositeException([
            new MessageContainingException(),
            new MessageContainingException(),
        ]);

        $violationList = $this->mapper->map($message, $exceptionAdapter);

        self::assertNotNull($violationList);
        self::assertCount(2, $violationList);

        /** @var ConstraintViolationInterface $violation1 */
        $violation1 = $violationList[0];

        self::assertSame('fallBackToExceptionMessage', $violation1->getPropertyPath());
        self::assertSame('Exception message to be used', $violation1->getMessage());

        /** @var ConstraintViolationInterface $violation2 */
        $violation2 = $violationList[1];

        // When #[Capture] message is specified as an empty string, it is used w/o any fallbacks to exception message
        self::assertSame('emptyTranslationMessage', $violation2->getPropertyPath());
        self::assertSame('', $violation2->getMessage());
    }
}
