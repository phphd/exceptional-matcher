<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @coversNothing
 *
 * @internal
 */
final class ExceptionToViolationListMatcherServiceTest extends BundleTestCase
{
    public function testExceptionMatcherService(): void
    {
        $exceptionMatcher = self::getContainer()->get(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>');
        self::assertInstanceOf(ExceptionMatcher::class, $exceptionMatcher);

        if (PhdExceptionalMatcherExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(ExceptionToViolationListMatcher::class, $exceptionMatcher);
        } else {
            self::assertNotInstanceOf(ExceptionToViolationListMatcher::class, $exceptionMatcher);
            self::assertInstanceOf(LazyObjectInterface::class, $exceptionMatcher);
            self::assertInstanceOf(ExceptionToViolationListMatcher::class, $exceptionMatcher->initializeLazyObject());
        }
    }
}
