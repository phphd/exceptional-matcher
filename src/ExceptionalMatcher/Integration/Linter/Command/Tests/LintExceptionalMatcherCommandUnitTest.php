<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Command\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Integration\Linter\Command\LintExceptionalMatcherCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

use function dirname;
use function json_decode;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Integration\Linter\Command\LintExceptionalMatcherCommand
 */
final class LintExceptionalMatcherCommandUnitTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var LintExceptionalMatcherCommand $command */
        $command = $container->get(LintExceptionalMatcherCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    public function testValidMappingsPass(): void
    {
        $exitCode = $this->commandTester->execute([
            'paths' => [$this->stubPath('PlannedItem.php', 'Rule/Object/Tests/Stub')],
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('1 classes scanned: 0 errors, 0 warnings.', $this->commandTester->getDisplay());
    }

    public function testBrokenMappingsFail(): void
    {
        $exitCode = $this->commandTester->execute([
            'paths' => [$this->stubPath('Invalid/UndefinedConstantConditionMessage.php')],
        ]);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('undefined_condition', $this->commandTester->getDisplay());
        self::assertStringContainsString('$caughtValue', $this->commandTester->getDisplay());
        self::assertStringContainsString('1 classes scanned: 1 errors, 0 warnings.', $this->commandTester->getDisplay());
    }

    public function testWarningsPassUnlessFailOnWarningIsSet(): void
    {
        $paths = [
            'paths' => [$this->stubPath('AbstractTryMessage.php')],
        ];

        self::assertSame(Command::SUCCESS, $this->commandTester->execute($paths));

        self::assertSame(Command::FAILURE, $this->commandTester->execute([
            ...$paths,
            '--fail-on-warning' => true,
        ]));
    }

    public function testReportsDefectsAsJson(): void
    {
        $exitCode = $this->commandTester->execute([
            'paths' => [$this->stubPath('Invalid/UndefinedConstantConditionMessage.php')],
            '--format' => 'json',
        ]);

        self::assertSame(Command::FAILURE, $exitCode);

        /** @var array{defects:list<array{severity:string,class:string,property:?string,message:string}>} $report */
        $report = json_decode($this->commandTester->getDisplay(), true, flags: JSON_THROW_ON_ERROR);

        [$defect] = $report['defects'];

        self::assertSame('error', $defect['severity']);
        self::assertSame('caughtValue', $defect['property']);
    }

    public function testRejectsUnknownFormat(): void
    {
        $exitCode = $this->commandTester->execute([
            'paths' => [$this->stubPath('AbstractTryMessage.php')],
            '--format' => 'xml',
        ]);

        self::assertSame(Command::INVALID, $exitCode);
        self::assertStringContainsString('Unknown format "xml"', $this->commandTester->getDisplay());
    }

    public function testFailsOnNonExistentPath(): void
    {
        $exitCode = $this->commandTester->execute([
            'paths' => [__DIR__.'/does-not-exist'],
        ]);

        self::assertSame(Command::INVALID, $exitCode);
        self::assertStringContainsString('does-not-exist', $this->commandTester->getDisplay());
    }

    private function stubPath(string $fileName, string $stubDirectory = 'Integration/Linter/Tests/Stub'): string
    {
        return dirname(__DIR__, 4).'/'.$stubDirectory.'/'.$fileName;
    }
}
