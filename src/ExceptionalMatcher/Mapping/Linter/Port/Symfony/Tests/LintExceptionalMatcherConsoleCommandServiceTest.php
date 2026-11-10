<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony\Tests;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony\LintExceptionalMatcherConsoleCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;

/**
 * @covers \PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalMatcherBundle
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
 *
 * @internal
 */
final class LintExceptionalMatcherConsoleCommandServiceTest extends BundleTestCase
{
    public function testCommandIsRegisteredInConsoleApplication(): void
    {
        $command = (new Application(self::bootKernel()))->find('lint:exceptional-matcher');

        self::assertInstanceOf(LazyCommand::class, $command);
        self::assertInstanceOf(LintExceptionalMatcherConsoleCommand::class, $command->getCommand());
    }
}
