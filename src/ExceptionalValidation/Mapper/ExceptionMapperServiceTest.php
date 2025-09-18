<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use PhPhD\ExceptionToolkit\Unwrapper\Messenger\MessengerExceptionUnwrapper;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @coversNothing
 *
 * @internal
 */
final class ExceptionMapperServiceTest extends BundleTestCase
{
    public function testExceptionMapperService(): void
    {
        $exceptionMapper = self::getContainer()->get(ExceptionMapper::class.'<non-empty-list<'.CapturedException::class.'<Throwable>>>');
        self::assertInstanceOf(ExceptionMapper::class, $exceptionMapper);
        self::assertNotInstanceOf(DefaultExceptionMapper::class, $exceptionMapper);
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionMapper);
        self::assertInstanceOf(DefaultExceptionMapper::class, $exceptionMapper->initializeLazyObject());
    }

    public function testExceptionUnwrapper(): void
    {
        $exceptionUnwrapper = self::getContainer()->get('phd_exceptional_validation.exception_unwrapper');
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionUnwrapper);
        self::assertFalse($exceptionUnwrapper->isLazyObjectInitialized());
        self::assertInstanceOf(MessengerExceptionUnwrapper::class, $exceptionUnwrapper->initializeLazyObject());
    }
}
