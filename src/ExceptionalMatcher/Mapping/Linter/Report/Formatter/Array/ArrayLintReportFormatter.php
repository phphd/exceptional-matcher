<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\Array;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;

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

        foreach ($report->getDefects() as $classReport) {
            foreach ($classReport->getDefects() as $defect) {
                $defects[] = [
                    'severity' => $defect->getSeverity()
                        ->value,
                    'class' => $defect->getLocation()
                        ->getClassName(),
                    'property' => $defect->getLocation()
                        ->getPropertyName(),
                    'message' => $defect->getMessage(),
                ];
            }
        }

        return $defects;
    }
}
