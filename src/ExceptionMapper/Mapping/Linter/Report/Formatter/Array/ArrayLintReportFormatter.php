<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Report\Formatter\Array;

use PhPhD\ExceptionMapper\Mapping\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport;

/**
 * @internal
 *
 * @implements LintReportFormatter<array<array-key,mixed>>
 */
final class ArrayLintReportFormatter implements LintReportFormatter
{
    /** @return array<array-key,mixed> */
    public function format(LintReport $report): array
    {
        $defects = [];

        foreach ($report->getClassReports() as $classReport) {
            foreach ($classReport->getDefects() as $defect) {
                $defects[] = [
                    'severity' => $defect->getSeverity()
                        ->value,
                    'class' => $defect->getLocation()
                        ->getClassName(),
                    'property' => $defect->getLocation()
                        ->getPropertyName(),
                    'messages' => $defect->getMessages(),
                ];
            }
        }

        return $defects;
    }
}
