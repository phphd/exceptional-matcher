<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;

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
