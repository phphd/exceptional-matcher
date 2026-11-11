<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Report\Formatter;

use PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport;

/**
 * Renders the lint report into the text of the requested output format.
 *
 * @internal
 *
 * @template TFormat
 */
interface LintReportFormatter
{
    /** @return TFormat */
    public function format(LintReport $report): mixed;
}
