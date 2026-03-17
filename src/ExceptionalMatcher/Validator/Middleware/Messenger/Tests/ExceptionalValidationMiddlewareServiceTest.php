<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Middleware\Messenger\Tests;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Validator\Middleware\Messenger\ExceptionalValidationMiddleware;

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
