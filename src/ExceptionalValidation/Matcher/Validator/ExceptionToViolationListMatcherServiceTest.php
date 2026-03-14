<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Matcher\Validator;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Matcher\ExceptionMatcher;
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

        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(ExceptionToViolationListMatcher::class, $exceptionMatcher);
        } else {
            self::assertNotInstanceOf(ExceptionToViolationListMatcher::class, $exceptionMatcher);
            self::assertInstanceOf(LazyObjectInterface::class, $exceptionMatcher);
            self::assertInstanceOf(ExceptionToViolationListMatcher::class, $exceptionMatcher->initializeLazyObject());
        }
    }
}
