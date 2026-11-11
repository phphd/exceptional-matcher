<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Port\Symfony\Tests;

use Closure;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Linter\Port\Symfony\LintExceptionMapperConsoleCommand;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\MessageWithNoTryAttribute;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Tester\CommandTester;

use function array_map;
use function explode;
use function implode;
use function rtrim;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Port\Symfony\LintExceptionMapperConsoleCommand
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Class\Path\ClassPathMappingLinter
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Port\Symfony\Formatter\ConsoleLintReportFormatter
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Port\LintMappingUseCase
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\Class\ClassReport
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\MappingDefect
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\Severity\DefectSeverity
 */
final class LintExceptionMapperConsoleCommandUnitTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(LintExceptionMapperConsoleCommand::class));

        $container->compile();

        /** @var LintExceptionMapperConsoleCommand $command */
        $command = $container->get(LintExceptionMapperConsoleCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    public function testErrorsAreRenderedAndFailTheCommand(): void
    {
        $exitCode = $this->commandTester->execute([
            '--symbol-format' => 'class-string',
            'symbols' => [UndefinedConstantConditionMessage::class],
        ]);

        self::assertSame(Command::FAILURE, $exitCode);

        $display = self::trim(self::undecorate($this->commandTester->getDisplay()));

        self::assertSame(
            <<<'TXT'
             ---------------------------------------------------------------------------------------------------------------
              src/ExceptionMapper/Mapping/Linter/Tests/Stub/Invalid/UndefinedConstantConditionMessage.php
             ---------------------------------------------------------------------------------------------------------------
              ✏️	   PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage::$divisor
              [ERROR]  Undefined constant
                       "PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Undefined\undefined_condition"
                       ↳ #[Catch_] attribute instantiation failed.
                         ↳ #[Catch_] exception mapping compilation failed.

              ✏️	   PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage
              [ERROR]  #[Try_] class does not define any #[Catch_] mappings and no nested matchable properties, so it never
                       matches anything.
                       ↳ Class exception mapping compilation has failed.

             ---------------------------------------------------------------------------------------------------------------



             [ERROR] 1 classes scanned: 2 errors found.
            TXT,
            $display,
        );
    }

    public function testWarningsAreRenderedAndFailTheCommand(): void
    {
        $exitCode = $this->commandTester->execute([
            '--symbol-format' => 'class-string',
            'symbols' => [MessageWithNoTryAttribute::class],
        ]);

        self::assertSame(Command::FAILURE, $exitCode);

        $display = self::undecorate($this->commandTester->getDisplay());

        self::assertStringContainsString(MessageWithNoTryAttribute::class.'::$property', $display);
        self::assertStringContainsString('[WARNING]  Properties declare #[Catch_] mappings', $display);
        self::assertStringContainsString('[WARNING] 1 classes scanned: 1 warning found.', $display);
    }

    /**
     * @param Closure(class-string):string $toSymbolFormat
     *
     * @dataProvider symbolFormatDataProvider
     */
    public function testProperlyMappedClassPassesTheCheck(string $symbolFormat, Closure $toSymbolFormat): void
    {
        $exitCode = $this->commandTester->execute([
            '--symbol-format' => $symbolFormat,
            'symbols' => [$toSymbolFormat(HandleableMessageStub::class)],
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('[OK] 1 classes scanned: no defects found.', $this->commandTester->getDisplay());
    }

    /** @return iterable<array{string,Closure(class-string):string}> */
    public static function symbolFormatDataProvider(): iterable
    {
        yield ['class-string', (static fn(string $className): string => $className)];

        yield ['path-string', self::filePathOf(...)];
    }

    public function testRendersOutputAtDesiredFormat(): void
    {
        $exitCode = $this->commandTester->execute([
            '--output-format' => 'json',
            'symbols' => [self::filePathOf(UndefinedConstantConditionMessage::class)],
        ]);

        self::assertSame(Command::FAILURE, $exitCode);

        self::assertJson($this->commandTester->getDisplay());
    }

    /** Escape sequences and the padding of the table cells obscure the layout */
    private static function undecorate(string $display): string
    {
        return Helper::removeDecoration(new OutputFormatter(), $display);
    }

    private static function trim(string $display): string
    {
        return rtrim(implode("\n", array_map(rtrim(...), explode("\n", $display))));
    }

    /** @param class-string $className */
    private static function filePathOf(string $className): string
    {
        return (string)(new ReflectionClass($className))->getFileName();
    }
}
