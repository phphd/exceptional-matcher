<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger\Tests;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger\ExceptionalValidationMiddleware;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

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

    /** @return list<CompilerPassInterface> */
    protected static function compilerPasses(): array
    {
        return [new PublicServiceCompilerPass('phd_exceptional_validation')];
    }
}
