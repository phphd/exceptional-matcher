<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Port;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\ExceptionMapper;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionToolkit\Unwrapper\Messenger\MessengerExceptionUnwrapper;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @covers \PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalMatcherBundle
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
 *
 * @internal
 */
final class MainExceptionMapperServiceTest extends BundleTestCase
{
    public function testExceptionMapperService(): void
    {
        $exceptionMapper = self::getContainer()->get(ExceptionMapper::class.'<'.MatchedExceptionList::class.'>');
        self::assertInstanceOf(ExceptionMapper::class, $exceptionMapper);

        if (PhdExceptionalMatcherExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(MainExceptionMapper::class, $exceptionMapper);
        } else {
            self::assertNotInstanceOf(MainExceptionMapper::class, $exceptionMapper);
            self::assertInstanceOf(LazyObjectInterface::class, $exceptionMapper);
            self::assertInstanceOf(MainExceptionMapper::class, $exceptionMapper->initializeLazyObject());
        }
    }

    public function testExceptionUnwrapper(): void
    {
        $exceptionUnwrapper = self::getContainer()->get('phd_exceptional_matcher.exception_unwrapper');
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionUnwrapper);
        self::assertFalse($exceptionUnwrapper->isLazyObjectInitialized());
        self::assertInstanceOf(MessengerExceptionUnwrapper::class, $exceptionUnwrapper->initializeLazyObject());
    }
}
