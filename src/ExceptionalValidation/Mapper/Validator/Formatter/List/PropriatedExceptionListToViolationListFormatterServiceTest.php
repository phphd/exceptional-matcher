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
final class PropriatedExceptionListToViolationListFormatterServiceTest extends BundleTestCase
{
    public function testViolationsListFormatter(): void
    {
        $violationsListFormatter = self::getContainer()->get(PropriatedExceptionListFormatter::class);
        self::assertInstanceOf(PropriatedExceptionListFormatter::class, $violationsListFormatter);

        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(PropriatedExceptionListToViolationListFormatter::class, $violationsListFormatter);
        } else {
            self::assertNotInstanceOf(PropriatedExceptionListToViolationListFormatter::class, $violationsListFormatter);
            self::assertInstanceOf(LazyObjectInterface::class, $violationsListFormatter);
            self::assertInstanceOf(PropriatedExceptionListToViolationListFormatter::class, $violationsListFormatter->initializeLazyObject());
        }
    }
}
