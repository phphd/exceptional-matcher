<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony\Formatter;

use Generator;
use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Class\ClassReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;

use function array_shift;
use function getcwd;
use function implode;
use function max;
use function sprintf;
use function str_pad;
use function str_repeat;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function substr_replace;
use function wordwrap;

/**
 * Renders the report as a table of defects per class, the way phpstan lists its errors per file.
 *
 * @internal
 *
 * @implements LintReportFormatter<string>
 */
final class ConsoleLintReportFormatter implements LintReportFormatter
{
    private const LOCATION_MARKER = '✏️';

    /** Terminals disagree on the marker width, yet all of them stop a tab at this column */
    private const LOCATION_TAB_STOP = 8;

    private const CAUSE_MARKER = '↳';

    private const CAUSE_INDENT = '  ';

    /** Narrow terminals wrap the messages rather than squeeze them into nothing */
    private const MIN_MESSAGE_WIDTH = 40;

    /** Table indentation, the two cell paddings and the closing border */
    private const TABLE_DECORATION_WIDTH = 4;

    /** Table indentation and the leading cell padding */
    private const CELL_OFFSET = 2;

    public function format(LintReport $report): string
    {
        $output = new BufferedOutput(decorated: true);
        $style = new SymfonyStyle(new ArrayInput([]), $output);

        foreach ($report->getClassReports() as $classReport) {
            $this->dumpClassReport($style, $classReport);
        }

        $this->dumpSummary($style, $report);

        return $output->fetch();
    }

    private function dumpClassReport(SymfonyStyle $style, ClassReport $classReport): void
    {
        $style
            ->createTable()
            ->setHeaders([$header = $this->classLocation($classReport)])
            ->setRows(iterator_to_array($this->defectRows($classReport->getDefects(), $header)))
            ->render()
        ;

        $style->newLine();
    }

    /**
     * Messages are wrapped here rather than by the table, which would wrap the header and the attribution too.
     *
     * Anything already too long for the terminal widens the table, so the messages may as well fill it out.
     *
     * @param non-empty-list<MappingDefect> $defects
     */
    private function cellWidthOf(array $defects, string $header): int
    {
        $width = max(
            self::MIN_MESSAGE_WIDTH,
            (new Terminal())->getWidth() - self::TABLE_DECORATION_WIDTH,
            Helper::width($header),
        );

        foreach ($defects as $defect) {
            $width = max($width, Helper::width($this->defectLocation($defect)));
        }

        return $width;
    }

    /** The source file, so that the terminal and the IDE can link right to it. */
    private function classLocation(ClassReport $classReport): string
    {
        $filePath = $classReport->getFilePath();

        if (null === $filePath) {
            return $classReport->getClassName();
        }

        return $this->relativePath($filePath);
    }

    private function relativePath(string $filePath): string
    {
        $workingDirectory = getcwd();

        if (false === $workingDirectory) {
            return $filePath;
        }

        $prefix = $workingDirectory.DIRECTORY_SEPARATOR;

        return str_starts_with($filePath, $prefix)
            ? substr($filePath, strlen($prefix))
            : $filePath;
    }

    /** @param non-empty-list<MappingDefect> $defects */
    private function defectRows(array $defects, string $header): Generator
    {
        $width = $this->cellWidthOf($defects, $header);

        foreach ($defects as $defect) {
            yield [$this->defect($defect, $width) . "\n"];
        }
    }

    private function defect(MappingDefect $defect, int $width): string
    {
        return implode("\n", [
            $this->decorate($this->defectLocation($defect), $defect->getSeverity()),
            $this->exceptionTraceMessage($defect, $width),
        ]);
    }

    /** The symbol aligns with the message, past the label */
    private function defectLocation(MappingDefect $defect): string
    {
        $location = $defect->getLocation()->toReference();

        $gap = self::CELL_OFFSET + $this->messageOffset($defect->getSeverity()) - self::LOCATION_TAB_STOP;

        return self::LOCATION_MARKER."\t".str_repeat(' ', $gap).$location;
    }

    /** The root cause leads, and every failure it went on to cause trails it. */
    private function exceptionTraceMessage(MappingDefect $defect, int $width): string
    {
        $severity = $defect->getSeverity();
        $messages = $defect->getMessages();

        $lines = [$this->rootExceptionMessage($severity, array_shift($messages), $width)];

        // Causes align with the root message, past its label
        $messageIndent = str_repeat(' ', $this->messageOffset($severity));

        foreach ($messages as $depth => $message) {
            $lines[] = $this->chainedExceptionMessage($message, $messageIndent, $depth, $width);
        }

        return implode("\n", $lines);
    }

    /** Errors carry no label: they are what the linter is expected to report. */
    private function rootExceptionMessage(DefectSeverity $severity, string $message, int $width): string
    {
        $label = $this->label($severity);

        $wrapped = wordwrap(str_pad($label, $this->messageOffset($severity)).$message, $width, "\n", cut_long_words: true);

        // Decorate the label after wrapping
        return $this->decorate($label, $severity).$this->italic(substr($wrapped, strlen($label)));
    }

    /** Each chained exception message nests one level deeper than the previous. */
    private function chainedExceptionMessage(string $message, string $baseIndent, int $depth, int $width): string
    {
        $indent = $baseIndent.str_repeat(self::CAUSE_INDENT, $depth);
        $offset = Helper::width($indent.self::CAUSE_MARKER.' ');

        $wrapped = wordwrap($message, max(self::MIN_MESSAGE_WIDTH, $width - $offset), "\n", cut_long_words: true);

        return sprintf(
            '%s<fg=gray>%s</> %s',
            $indent,
            self::CAUSE_MARKER,
            // Continuation lines align under the text rather than under the arrow
            $this->italic(str_replace("\n", "\n".str_repeat(' ', $offset), $wrapped)),
        );
    }

    /** Raw SGR codes, since Symfony styles know no italic; reset per line, as the table renders each line apart */
    private function italic(string $text): string
    {
        return "\e[3m".str_replace("\n", "\e[23m\n\e[3m", $text)."\e[23m";
    }

    /** Where the message starts: two columns past the label */
    private function messageOffset(DefectSeverity $severity): int
    {
        return strlen($this->label($severity)) + 2;
    }

    private function label(DefectSeverity $severity): string
    {
        return '['.strtoupper($severity->value).']';
    }

    private function decorate(string $text, DefectSeverity $severity): string
    {
        $color = match ($severity) {
            DefectSeverity::Error => 'red',
            DefectSeverity::Warning => 'yellow',
            DefectSeverity::Notice => 'blue',
        };

        return sprintf('<fg=%s>%s</>', $color, $text);
    }

    private function dumpSummary(SymfonyStyle $style, LintReport $report): void
    {
        $summary = sprintf(
            '%d classes scanned: %d errors, %d warnings.',
            $report->getScannedSymbols(),
            $errors = $report->countOf(DefectSeverity::Error),
            $warnings = $report->countOf(DefectSeverity::Warning),
        );

        if ($errors > 0) {
            $style->error($summary);

            return;
        }

        if ($warnings > 0) {
            $style->warning($summary);

            return;
        }

        $style->success($summary);
    }
}
