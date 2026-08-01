<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter;

use PhPhD\ExceptionalMatcher\Integration\Linter\Report\LintReport;

/**
 * Renders the lint report into the text of the requested output format.
 *
 * @internal
 */
interface LintReportFormatter
{
    public function format(LintReport $report): string;
}
