<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Format\Array;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Format\LintReportFormatter;

/**
 * @internal
 *
 * @implements LintReportFormatter<array>
 */
final class ArrayLintReportFormatter implements LintReportFormatter
{
    public function format(LintReport $report): array
    {
        $defects = [];

        foreach ($report->getDefects() as $defect) {
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

        return $defects;
    }
}
