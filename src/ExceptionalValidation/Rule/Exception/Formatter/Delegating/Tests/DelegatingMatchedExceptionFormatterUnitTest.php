<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Validator\Formatter\ExceptionViolationFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\DelegatingMatchedExceptionFormatter
 *
 * @internal
 */
final class DelegatingMatchedExceptionFormatterUnitTest extends TestCase
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

        $container->register(CustomExceptionViolationFormatter::class, CustomExceptionViolationFormatter::class)
            ->setArguments([new Reference(ExceptionViolationFormatter::class.'<Throwable>')])
            ->setAutoconfigured(true)
        ;

        $container->compile();

        /** @var ExceptionMatcher<ConstraintViolationListInterface> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->matcher = $matcher;
    }

    public function testCustomViolationFormatter(): void
    {
        $originalException = new CustomFormattedException();
        $message = HandleableMessageStub::create();

        $violationList = $this->matcher->match($originalException, $message);

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
