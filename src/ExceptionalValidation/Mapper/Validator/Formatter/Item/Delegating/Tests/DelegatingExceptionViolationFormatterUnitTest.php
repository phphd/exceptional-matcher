<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter
 *
 * @internal
 */
final class DelegatingExceptionViolationFormatterUnitTest extends TestCase
{
    /** @var ExceptionMapper<ConstraintViolationListInterface> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->register(CustomExceptionViolationFormatter::class, CustomExceptionViolationFormatter::class)
            ->setArguments([new Reference(ExceptionViolationFormatter::class.'<Throwable>')])
            ->setAutoconfigured(true)
        ;

        $container->compile();

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->mapper = $mapper;
    }

    public function testCustomViolationFormatter(): void
    {
        $message = HandleableMessageStub::create();

        $originalException = new CustomFormattedException();

        $violationList = $this->mapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('custom - oops', $violation->getMessage());
        self::assertSame('custom.oops', $violation->getMessageTemplate());
        self::assertSame([
            'custom' => 'param',
        ], $violation->getParameters());
        self::assertSame('custom.formatted', $violation->getPropertyPath());
    }
}
