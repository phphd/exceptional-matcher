<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\Integration\Symfony;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Defect\DefectSeverity;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

/**
 * Renders the report as decorated console output.
 *
 * @internal
 *
 * @implements LintReportFormatter<string>
 */
final class ConsoleLintReportFormatter implements LintReportFormatter
{
    public function format(LintReport $report): string
    {
        $output = new BufferedOutput(decorated: true);
        $style = new SymfonyStyle(new ArrayInput([]), $output);

        $previousClassName = null;

        foreach ($report->getDefects() as $defect) {
            $location = $defect->getLocation();

            if ($location->getClassName() !== $previousClassName) {
                $previousClassName = $location->getClassName();

                $style->writeln(sprintf(' <fg=red>✗</> %s', $previousClassName));
            }

            $style->writeln(sprintf(
                '     [%s] %s%s',
                $defect->getSeverity()
                    ->value,
                null !== $location->getPropertyName() ? sprintf('$%s: ', $location->getPropertyName()) : '',
                $defect->getMessage(),
            ));
        }

        if ($report->hasDefects()) {
            $style->newLine();
        }

        $this->writeSummary($style, $report);

        return $output->fetch();
    }

    private function writeSummary(SymfonyStyle $style, LintReport $report): void
    {
        $errors = $report->countOf(DefectSeverity::Error);
        $warnings = $report->countOf(DefectSeverity::Warning);

        $summary = sprintf(
            '%d classes scanned: %d errors, %d warnings.',
            $report->getScannedClasses(),
            $errors,
            $warnings,
        );

        if ($errors > 0) {
            $style->error($summary);

            return;
        }

        if ($warnings > 0) {
            $style->warning($summary);

            return;
        }

        $style->success($summary);
    }
}
