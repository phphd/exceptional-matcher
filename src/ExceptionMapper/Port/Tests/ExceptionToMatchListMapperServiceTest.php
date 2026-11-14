<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatchList;
use PhPhD\ExceptionMapper\Port\ExceptionToMatchListMapper;
use PhPhD\ExceptionToolkit\Unwrapper\Messenger\MessengerExceptionUnwrapper;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @covers \PhPhD\ExceptionMapper\Bundle\PhdExceptionMapperBundle
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 *
 * @internal
 */
final class ExceptionToMatchListMapperServiceTest extends BundleTestCase
{
    public function testExceptionMapperService(): void
    {
        $exceptionMapper = self::getContainer()->get(ExceptionMapper::class.'<'.ExceptionMatchList::class.'>');
        self::assertInstanceOf(ExceptionMapper::class, $exceptionMapper);

        if (PhdExceptionMapperExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(ExceptionToMatchListMapper::class, $exceptionMapper);
        } else {
            self::assertNotInstanceOf(ExceptionToMatchListMapper::class, $exceptionMapper);
            self::assertInstanceOf(LazyObjectInterface::class, $exceptionMapper);
            self::assertInstanceOf(ExceptionToMatchListMapper::class, $exceptionMapper->initializeLazyObject());
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
