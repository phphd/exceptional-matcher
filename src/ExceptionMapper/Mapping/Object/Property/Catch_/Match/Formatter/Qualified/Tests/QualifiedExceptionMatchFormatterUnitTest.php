<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\RegisterCustomViolationFormatterCompilerPass;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\CustomFormattedMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\QualifiedExceptionMatchFormatter
 *
 * @internal
 */
final class QualifiedExceptionMatchFormatterUnitTest extends TestCase
{
    /** @var ExceptionMapper<ConstraintViolationListInterface> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new RegisterCustomViolationFormatterCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, RegisterCustomViolationFormatterCompilerPass::PRIORITY);

        $container->compile();

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->mapper = $mapper;
    }

    public function testCustomViolationFormatter(): void
    {
        $message = new CustomFormattedMessage();
        $originalException = new CustomFormattedException();

        $violationList = $this->mapper->map($originalException, $message);

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
