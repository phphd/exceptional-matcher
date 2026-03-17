<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionToolkit\Unwrapper\Messenger\MessengerExceptionUnwrapper;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @coversNothing
 *
 * @internal
 */
final class MainExceptionMatcherServiceTest extends BundleTestCase
{
    public function testExceptionMatcherService(): void
    {
        $exceptionMatcher = self::getContainer()->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        self::assertInstanceOf(ExceptionMatcher::class, $exceptionMatcher);

        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(MainExceptionMatcher::class, $exceptionMatcher);
        } else {
            self::assertNotInstanceOf(MainExceptionMatcher::class, $exceptionMatcher);
            self::assertInstanceOf(LazyObjectInterface::class, $exceptionMatcher);
            self::assertInstanceOf(MainExceptionMatcher::class, $exceptionMatcher->initializeLazyObject());
        }
    }

    public function testExceptionUnwrapper(): void
    {
        $exceptionUnwrapper = self::getContainer()->get('phd_exceptional_validation.exception_unwrapper');
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionUnwrapper);
        self::assertFalse($exceptionUnwrapper->isLazyObjectInitialized());
        self::assertInstanceOf(MessengerExceptionUnwrapper::class, $exceptionUnwrapper->initializeLazyObject());
    }
}
