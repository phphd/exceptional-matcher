<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Command;

use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\DefectSeverity;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Integration\Linter\Discovery\ClassMapDiscovery;
use PhPhD\ExceptionalMatcher\Integration\Linter\MappingLinter;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function count;
use function json_encode;
use function sprintf;

/** @api */
#[AsCommand(
    name: 'lint:exceptional-matcher',
    description: 'Lints the #[Try_] / #[Catch_] exception mappings of the classes within the given paths',
)]
final class LintExceptionalMatcherCommand extends Command
{
    public function __construct(
        private readonly MappingLinter $linter,
        private readonly ClassMapDiscovery $discovery,
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

        try {
            $classNames = [...$this->discovery->discover($paths)];
        } catch (RuntimeException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return self::INVALID;
        }

        $defects = $this->linter->lint($classNames);

        match ($format) {
            'json' => $this->writeJson($defects, $output),
            'txt' => $this->writeText(count($classNames), $defects, $output),
            default => $output->writeln(sprintf('<error>Unknown format "%s": expected "txt" or "json".</error>', $format)),
        };

        if ('json' !== $format && 'txt' !== $format) {
            return self::INVALID;
        }

        if ($this->countOf(DefectSeverity::Error, $defects) > 0) {
            return self::FAILURE;
        }

        if ($failOnWarning && [] !== $defects) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /** @param list<MappingDefect> $defects */
    private function writeText(int $scannedClasses, array $defects, OutputInterface $output): void
    {
        $previousClassName = null;

        foreach ($defects as $defect) {
            $location = $defect->getLocation();

            if ($location->getClassName() !== $previousClassName) {
                $previousClassName = $location->getClassName();
                $output->writeln(sprintf(' <fg=red>✗</> %s', $previousClassName));
            }

            $output->writeln(sprintf(
                '     [%s] %s%s',
                $defect->getSeverity()
                    ->value,
                null !== $location->getPropertyName() ? sprintf('$%s: ', $location->getPropertyName()) : '',
                $defect->getMessage(),
            ));
        }

        if ([] !== $defects) {
            $output->writeln('');
        }

        $output->writeln(sprintf(
            ' %d classes scanned: %d errors, %d warnings.',
            $scannedClasses,
            $this->countOf(DefectSeverity::Error, $defects),
            $this->countOf(DefectSeverity::Warning, $defects),
        ));
    }

    /** @param list<MappingDefect> $defects */
    private function writeJson(array $defects, OutputInterface $output): void
    {
        $report = [];

        foreach ($defects as $defect) {
            $report[] = [
                'severity' => $defect->getSeverity()
                    ->value,
                'class' => $defect->getLocation()
                    ->getClassName(),
                'property' => $defect->getLocation()
                    ->getPropertyName(),
                'message' => $defect->getMessage(),
            ];
        }

        $output->writeln(json_encode(
            [
                'defects' => $report,
            ],
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ));
    }

    /** @param list<MappingDefect> $defects */
    private function countOf(DefectSeverity $severity, array $defects): int
    {
        $count = 0;

        foreach ($defects as $defect) {
            if ($defect->getSeverity() === $severity) {
                ++$count;
            }
        }

        return $count;
    }
}
