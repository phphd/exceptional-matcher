<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Validator\Formatter\Main\Tests;

use LogicException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Validator\Formatter\Main\Tests\Stub\MessageContainingException;
use PhPhD\ExceptionalValidation\Validator\Formatter\Main\Tests\Stub\ObjectPropertyMatchedException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \PhPhD\ExceptionalValidation\Validator\Formatter\Main\MainExceptionViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\MatchExceptionRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyMatchingRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\ObjectMatchingRuleSet
 *
 * @internal
 */
final class MainExceptionViolationFormatterUnitTest extends TestCase
{
    /** @var ExceptionMatcher<ConstraintViolationListInterface> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

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

        /** @var ExceptionMatcher<ConstraintViolationListInterface> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->matcher = $matcher;
    }

    public function testFormatException(): void
    {
        $originalException = new AnException();
        $message = HandleableMessageStub::create();

        $violationList = $this->matcher->match($originalException, $message);

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
        $exception = new LogicException();
        $message = HandleableMessageStub::create()->withMessageText('invalid text value');

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->matcher->match($exception, $message);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame('invalid text value', $violation->getInvalidValue());
    }

    public function testObjectInvalidValueIsCollected(): void
    {
        $originalException = new ObjectPropertyMatchedException();
        $message = HandleableMessageStub::create()->withObjectProperty($object = new stdClass());

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->matcher->match($originalException, $message);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame($object, $violation->getInvalidValue());
    }

    public function testViolationMessageFallsBackToExceptionMessage(): void
    {
        $exceptionAdapter = new CompositeException([
            new MessageContainingException(),
            new MessageContainingException(),
        ]);
        $message = HandleableMessageStub::create();

        $violationList = $this->matcher->match($exceptionAdapter, $message);

        self::assertNotNull($violationList);
        self::assertCount(2, $violationList);

        /** @var ConstraintViolationInterface $violation1 */
        $violation1 = $violationList[0];

        self::assertSame('fallBackToExceptionMessage', $violation1->getPropertyPath());
        self::assertSame('Exception message to be used', $violation1->getMessage());

        /** @var ConstraintViolationInterface $violation2 */
        $violation2 = $violationList[1];

        // When #[Catch_] message is specified as an empty string, it is used w/o any fallbacks to exception message
        self::assertSame('emptyTranslationMessage', $violation2->getPropertyPath());
        self::assertSame('', $violation2->getMessage());
    }
}
