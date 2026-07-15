<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @covers \PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\DelegatingMatchedExceptionFormatter
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

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
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
        $message = HandleableMessageStub::create();
        $originalException = new CustomFormattedException();

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
