<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @coversNothing
 *
 * @internal
 */
final class ExceptionListViolationFormatterServiceTest extends BundleTestCase
{
    public function testViolationsListFormatter(): void
    {
        $violationsListFormatter = self::getContainer()->get(ExceptionListViolationFormatter::class);
        self::assertInstanceOf(ExceptionListViolationFormatter::class, $violationsListFormatter);

        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(DefaultExceptionListViolationFormatter::class, $violationsListFormatter);
        } else {
            self::assertNotInstanceOf(DefaultExceptionListViolationFormatter::class, $violationsListFormatter);
            self::assertInstanceOf(LazyObjectInterface::class, $violationsListFormatter);
            self::assertInstanceOf(DefaultExceptionListViolationFormatter::class, $violationsListFormatter->initializeLazyObject());
        }
    }
}
