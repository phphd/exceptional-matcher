<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port;

use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @internal
 *
 * @template TSymbol of string
 * @template TFormat
 */
final class LintMappingUseCase
{
    /** @api */
    public function __construct(
        /** @var ContainerInterface<string,MappingLinter<TSymbol,LintReport>> */
        private readonly ContainerInterface $mappingLinterRegistry,
        /** @var ContainerInterface<string,LintReportFormatter<TFormat>> */
        private readonly ContainerInterface $reportFormatterRegistry,
    ) {
    }

    /**
     * @param iterable<TSymbol> $symbols
     *
     * @return array{LintReport,TFormat}
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @psalm-suppress UnnecessaryVarAnnotation
     */
    public function lint(string $symbolFormat, iterable $symbols, string $outputFormat): array
    {
        /** @var MappingLinter<TSymbol,LintReport> $linter */
        $linter = $this->mappingLinterRegistry->get($symbolFormat);

        $report = $linter->lint($symbols);

        /** @var LintReportFormatter<TFormat> $formatter */
        $formatter = $this->reportFormatterRegistry->get($outputFormat);

        $output = $formatter->format($report);

        return [$report, $output];
    }
}
