<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter\Json;

use PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Report\LintReport;

use function json_encode;

/** @internal */
final class JsonLintReportFormatter implements LintReportFormatter
{
    public function format(LintReport $report): string
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

        return json_encode(
            [
                'defects' => $defects,
            ],
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        );
    }
}
