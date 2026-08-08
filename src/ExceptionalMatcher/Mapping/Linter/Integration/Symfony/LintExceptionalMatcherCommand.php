<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Integration\Symfony;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Defect\DefectSeverity;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Format\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\UseCase\LintMappingUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

/**
 * @template TSymbol
 * @template TFormat
 *
 * @internal
 */
#[AsCommand(
    name: 'lint:exceptional-matcher',
    description: 'Lints the #[Try_] / #[Catch_] exception mappings of the classes within the given paths',
)]
final class LintExceptionalMatcherCommand extends Command
{
    public function __construct(
        /** @var LintMappingUseCase<TSymbol,TFormat> */
        private LintMappingUseCase $lintMapping,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('symbols', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Files or directories to scan for mapped classes')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format ("txt" or "json")', 'txt')
            ->addOption('fail-on-warning', null, InputOption::VALUE_NONE, 'Exit with a non-zero code when warnings are reported');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->style = new SymfonyStyle($input, $output);

        /** @var list<TSymbol> $symbols */
        $symbols = $input->getArgument('symbols');

        /** @var string $format */
        $format = $input->getOption('format');

        $failOnWarning = (bool)$input->getOption('fail-on-warning');

        if (!$this->reportFormatterRegistry->has($format)) {
            $output->writeln(sprintf('<error>Unknown format "%s".</error>', $format));

            return self::INVALID;
        }

        /** @var LintReportFormatter $reportFormatter */
        $reportFormatter = $this->reportFormatterRegistry->get($format);

        $output->writeln($reportFormatter->format($report));

        if ($report->countOf(DefectSeverity::Error) > 0) {
            return self::FAILURE;
        }

        if ($failOnWarning && $report->hasDefects()) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
