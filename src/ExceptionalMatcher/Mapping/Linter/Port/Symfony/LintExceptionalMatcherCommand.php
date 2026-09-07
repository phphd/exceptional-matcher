<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Port\LintMappingUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/** @internal */
#[AsCommand(
    name: 'lint:exceptional-matcher',
    description: 'Lints the #[Try_] / #[Catch_] exception mappings of the classes within the given paths',
)]
final class LintExceptionalMatcherCommand extends Command
{
    public function __construct(
        /** @var LintMappingUseCase<string,string|array<array-key,mixed>> */
        private readonly LintMappingUseCase $linter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('input-format', '-i', InputOption::VALUE_REQUIRED, 'Input format (path-string, class-string, etc.)', 'path-string')
            ->addArgument('symbols', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Files or directories to scan for mapped classes')
            ->addOption('output-format', '-o', InputOption::VALUE_REQUIRED, 'Output format (console, json, etc.)', 'console')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $inputFormat */
        $inputFormat = $input->getOption('input-format');

        /** @var list<string> $symbols */
        $symbols = $input->getArgument('symbols');

        /** @var string $outputFormat */
        $outputFormat = $input->getOption('output-format');

        [$hasDefects, $outputMessage] = $this->linter->lint($inputFormat, $symbols, $outputFormat);

        $output->writeln($outputMessage);

        return (int)$hasDefects;
    }
}
