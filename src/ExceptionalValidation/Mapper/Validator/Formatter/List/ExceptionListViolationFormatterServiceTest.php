<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List;

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
        $violationsListFormatter = self::getContainer()->get('phd_exceptional_validation.violations_list_formatter');
        self::assertInstanceOf(ExceptionListViolationFormatter::class, $violationsListFormatter);
        self::assertNotInstanceOf(DefaultExceptionListViolationFormatter::class, $violationsListFormatter);
        self::assertInstanceOf(LazyObjectInterface::class, $violationsListFormatter);
        self::assertInstanceOf(DefaultExceptionListViolationFormatter::class, $violationsListFormatter->initializeLazyObject());
    }
}
