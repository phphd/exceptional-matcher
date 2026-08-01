<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Command;

use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\DefectSeverity;
use PhPhD\ExceptionalMatcher\Integration\Linter\Discovery\ClassNameDiscovery;
use PhPhD\ExceptionalMatcher\Integration\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Report\LintReport;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function sprintf;

/** @internal */
#[AsCommand(
    name: 'lint:exceptional-matcher',
    description: 'Lints the #[Try_] / #[Catch_] exception mappings of the classes within the given paths',
)]
final class LintExceptionalMatcherCommand extends Command
{
    public function __construct(
        private readonly MappingLinter $linter,
        private readonly ClassNameDiscovery $classNameDiscovery,
        /** @var ContainerInterface<string,LintReportFormatter> */
        private readonly ContainerInterface $reportFormatterRegistry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Files or directories to scan for mapped classes')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format ("txt" or "json")', 'txt')
            ->addOption('fail-on-warning', null, InputOption::VALUE_NONE, 'Exit with a non-zero code when warnings are reported')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var list<string> $paths */
        $paths = $input->getArgument('paths');
        /** @var string $format */
        $format = $input->getOption('format');
        $failOnWarning = (bool)$input->getOption('fail-on-warning');

        if (!$this->reportFormatterRegistry->has($format)) {
            $output->writeln(sprintf('<error>Unknown format "%s".</error>', $format));

            return self::INVALID;
        }

        try {
            $classNames = $this->classNameDiscovery->discover($paths);
        } catch (RuntimeException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return self::INVALID;
        }

        $report = new LintReport(count($classNames), $this->linter->lint($classNames));

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
