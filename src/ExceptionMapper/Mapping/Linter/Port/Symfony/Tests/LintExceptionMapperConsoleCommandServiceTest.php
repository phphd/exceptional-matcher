<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Port\Symfony\Tests;

use PhPhD\ExceptionMapper\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionMapper\Mapping\Linter\Port\Symfony\LintExceptionMapperConsoleCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;

/**
 * @covers \PhPhD\ExceptionMapper\Bundle\PhdExceptionMapperBundle
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 *
 * @internal
 */
final class LintExceptionMapperConsoleCommandServiceTest extends BundleTestCase
{
    public function testCommandIsRegisteredInConsoleApplication(): void
    {
        $command = (new Application(self::bootKernel()))->find('lint:exception-mapper');

        self::assertInstanceOf(LazyCommand::class, $command);
        self::assertInstanceOf(LintExceptionMapperConsoleCommand::class, $command->getCommand());
    }
}
