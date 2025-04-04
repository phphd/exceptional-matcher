<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionToolkit\Unwrapper\Messenger\MessengerExceptionUnwrapper;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @coversNothing
 *
 * @internal
 */
final class ExceptionHandlerServiceTest extends BundleTestCase
{
    public function testExceptionHandlerService(): void
    {
        $exceptionHandler = self::getContainer()->get('phd_exceptional_validation.exception_handler');
        self::assertInstanceOf(ExceptionHandler::class, $exceptionHandler);
        self::assertNotInstanceOf(DefaultExceptionHandler::class, $exceptionHandler);
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionHandler);
        self::assertInstanceOf(DefaultExceptionHandler::class, $exceptionHandler->initializeLazyObject());
    }

    public function testExceptionUnwrapper(): void
    {
        $exceptionUnwrapper = self::getContainer()->get('phd_exceptional_validation.exception_unwrapper');
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionUnwrapper);
        self::assertFalse($exceptionUnwrapper->isLazyObjectInitialized());
        self::assertInstanceOf(MessengerExceptionUnwrapper::class, $exceptionUnwrapper->initializeLazyObject());
    }
}
