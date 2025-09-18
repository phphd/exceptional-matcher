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
    public function testExceptionMapperService(): void
    {
        $exceptionMapper = self::getContainer()->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        self::assertInstanceOf(ExceptionMapper::class, $exceptionMapper);
        self::assertNotInstanceOf(ExceptionViolationListMapper::class, $exceptionMapper);
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionMapper);
        self::assertInstanceOf(ExceptionViolationListMapper::class, $exceptionMapper->initializeLazyObject());
    }
}
