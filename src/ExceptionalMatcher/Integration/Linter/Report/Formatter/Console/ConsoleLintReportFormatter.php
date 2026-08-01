<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter\Console;

use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\DefectSeverity;
use PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Report\LintReport;

use function implode;
use function sprintf;

/**
 * Renders the report as decorated console output.
 *
 * @internal
 */
final class ConsoleLintReportFormatter implements LintReportFormatter
{
    public function format(LintReport $report): string
    {
        $lines = [];
        $previousClassName = null;

        foreach ($report->getDefects() as $defect) {
            $location = $defect->getLocation();

            if ($location->getClassName() !== $previousClassName) {
                $previousClassName = $location->getClassName();
                $lines[] = sprintf(' <fg=red>✗</> %s', $previousClassName);
            }

            $lines[] = sprintf(
                '     [%s] %s%s',
                $defect->getSeverity()
                    ->value,
                null !== $location->getPropertyName() ? sprintf('$%s: ', $location->getPropertyName()) : '',
                $defect->getMessage(),
            );
        }

        if ($report->hasDefects()) {
            $lines[] = '';
        }

        $lines[] = sprintf(
            ' %d classes scanned: %d errors, %d warnings.',
            $report->getScannedClasses(),
            $report->countOf(DefectSeverity::Error),
            $report->countOf(DefectSeverity::Warning),
        );

        return implode("\n", $lines);
    }
}
