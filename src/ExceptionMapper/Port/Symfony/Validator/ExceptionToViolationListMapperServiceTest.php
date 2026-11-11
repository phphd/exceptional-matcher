<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port\Symfony\Validator;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionMapper\ExceptionMapper;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @covers \PhPhD\ExceptionMapper\Bundle\PhdExceptionMapperBundle
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 *
 * @internal
 */
final class ExceptionToViolationListMapperServiceTest extends BundleTestCase
{
    public function testExceptionMapperService(): void
    {
        $exceptionMapper = self::getContainer()->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        self::assertInstanceOf(ExceptionMapper::class, $exceptionMapper);

        if (PhdExceptionMapperExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(ExceptionToViolationListMapper::class, $exceptionMapper);
        } else {
            self::assertNotInstanceOf(ExceptionToViolationListMapper::class, $exceptionMapper);
            self::assertInstanceOf(LazyObjectInterface::class, $exceptionMapper);
            self::assertInstanceOf(ExceptionToViolationListMapper::class, $exceptionMapper->initializeLazyObject());
        }
    }
}
