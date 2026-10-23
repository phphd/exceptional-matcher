<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony\Tests;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony\LintExceptionalMatcherCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;

/**
 * @coversNothing
 *
 * @internal
 */
final class LintExceptionalMatcherCommandServiceTest extends BundleTestCase
{
    public function testCommandIsRegisteredInConsoleApplication(): void
    {
        $command = (new Application(self::bootKernel()))->find('lint:exceptional-matcher');

        self::assertInstanceOf(LazyCommand::class, $command);
        self::assertInstanceOf(LintExceptionalMatcherCommand::class, $command->getCommand());
    }
}
