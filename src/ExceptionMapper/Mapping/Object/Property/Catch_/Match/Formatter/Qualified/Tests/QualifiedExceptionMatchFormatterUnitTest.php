<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart\AddProductToCartCommand;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart\OutOfStockExceptionMatchFormatterCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart\OutOfStockProductException;
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

        $container->addCompilerPass(new OutOfStockExceptionMatchFormatterCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, OutOfStockExceptionMatchFormatterCompilerPass::PRIORITY);

        $container->compile();

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->mapper = $mapper;
    }

    public function testExceptionCanBeFormattedUsingACustomService(): void
    {
        $command = new AddProductToCartCommand(productId: 'tshirt-xl', quantity: 5);
        $exception = OutOfStockProductException::create(availableQuantity: 3);

        $violationList = $this->mapper->map($exception, $command);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('Only 3 left in stock. Reduce the quantity by 2 items.', $violation->getMessage());
        self::assertSame('Only {{ available }} left in stock. Reduce the quantity by {{ shortage }} items.', $violation->getMessageTemplate());
        self::assertSame([
            '{{ available }}' => '3',
            '{{ shortage }}' => '2',
        ], $violation->getParameters());
        self::assertSame(2, $violation->getPlural());
        self::assertSame('quantity', $violation->getPropertyPath());
        self::assertSame(5, $violation->getInvalidValue());
    }
}
