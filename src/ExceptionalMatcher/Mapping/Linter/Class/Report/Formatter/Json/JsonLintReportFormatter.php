<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\Formatter\Json;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\LintReport;

use function json_encode;

/**
 * @internal
 *
 * @implements LintReportFormatter<string>
 */
final class JsonLintReportFormatter implements LintReportFormatter
{
    public function __construct(
        /** @var LintReportFormatter<array<array-key,mixed>> */
        private readonly LintReportFormatter $arrayFormatter,
    ) {
    }

    public function format(LintReport $report): string
    {
        return json_encode(
            [
                'defects' => $this->arrayFormatter->format($report),
            ],
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        );
    }
}
