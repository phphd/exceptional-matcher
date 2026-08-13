<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Integration\Symfony;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class LintExceptionalMatcherCommandServiceTest extends BundleTestCase
{
    public function testLintCommandService(): void
    {
        $command = self::getContainer()->get(LintExceptionalMatcherCommand::class);

        self::assertInstanceOf(LintExceptionalMatcherCommand::class, $command);
        self::assertSame('lint:exceptional-matcher', $command->getName());
    }
}
