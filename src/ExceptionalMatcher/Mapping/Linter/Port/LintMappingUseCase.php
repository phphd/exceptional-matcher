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
 * @template TSymbol
 * @template TFormat
 */
final class LintMappingUseCase
{
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
     * @return TFormat
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function lint(iterable $symbols, string $inputFormat, string $outputFormat): mixed
    {
        /** @var MappingLinter<LintReport> $linter */
        $linter = $this->mappingLinterRegistry->get($inputFormat);

        $report = $linter->lint($symbols);

        /** @var LintReportFormatter<mixed> $formatter */
        $formatter = $this->reportFormatterRegistry->get($outputFormat);

        return $formatter->format($report);
    }
}
