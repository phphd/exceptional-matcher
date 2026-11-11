<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatchList;
use PhPhD\ExceptionToolkit\Unwrapper\Messenger\MessengerExceptionUnwrapper;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @covers \PhPhD\ExceptionMapper\Bundle\PhdExceptionMapperBundle
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 *
 * @internal
 */
final class MainExceptionMapperServiceTest extends BundleTestCase
{
    public function testExceptionMapperService(): void
    {
        $exceptionMapper = self::getContainer()->get(ExceptionMapper::class.'<'.ExceptionMatchList::class.'>');
        self::assertInstanceOf(ExceptionMapper::class, $exceptionMapper);

        if (PhdExceptionMapperExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(MainExceptionMapper::class, $exceptionMapper);
        } else {
            self::assertNotInstanceOf(MainExceptionMapper::class, $exceptionMapper);
            self::assertInstanceOf(LazyObjectInterface::class, $exceptionMapper);
            self::assertInstanceOf(MainExceptionMapper::class, $exceptionMapper->initializeLazyObject());
        }
    }

    public function testExceptionUnwrapper(): void
    {
        $exceptionUnwrapper = self::getContainer()->get('phd_exception_mapper.exception_unwrapper');
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionUnwrapper);
        self::assertFalse($exceptionUnwrapper->isLazyObjectInitialized());
        self::assertInstanceOf(MessengerExceptionUnwrapper::class, $exceptionUnwrapper->initializeLazyObject());
    }
}
