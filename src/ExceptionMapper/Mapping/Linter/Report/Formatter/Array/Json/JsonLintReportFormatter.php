<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Report\Formatter\Array\Json;

use JsonException;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport;

use function json_encode;

/**
 * @internal
 *
 * @implements LintReportFormatter<string>
 */
final class JsonLintReportFormatter implements LintReportFormatter
{
    /** @api */
    public function __construct(
        /** @var LintReportFormatter<array<array-key,mixed>> */
        private readonly LintReportFormatter $arrayFormatter,
    ) {
    }

    /** @throws JsonException */
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
