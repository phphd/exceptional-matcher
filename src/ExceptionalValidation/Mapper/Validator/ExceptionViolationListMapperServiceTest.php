<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @coversNothing
 *
 * @internal
 */
final class ExceptionViolationListMapperServiceTest extends BundleTestCase
{
    public function testExceptionHandlerService(): void
    {
        $exceptionHandler = self::getContainer()->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        self::assertInstanceOf(ExceptionMapper::class, $exceptionHandler);
        self::assertNotInstanceOf(ExceptionViolationListMapper::class, $exceptionHandler);
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionHandler);
        self::assertInstanceOf(ExceptionViolationListMapper::class, $exceptionHandler->initializeLazyObject());
    }
}
