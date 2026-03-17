<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Validator\Middleware\Messenger\Tests;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Validator\Middleware\Messenger\ExceptionalValidationMiddleware;

/**
 * @coversNothing
 *
 * @internal
 */
final class ExceptionalValidationMiddlewareServiceTest extends BundleTestCase
{
    public function testMiddlewareService(): void
    {
        $middleware = self::getContainer()->get('phd_exceptional_validation');

        self::assertInstanceOf(ExceptionalValidationMiddleware::class, $middleware);
    }
}
