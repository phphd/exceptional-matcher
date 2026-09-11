<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony\Formatter;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Class\ClassReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
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
    /** Marks the class or property the defect belongs to, where phpstan puts the file path */
    private const LOCATION_MARKER = '✏️';

    /** Leads every failure that the root cause went on to trigger */
    private const CAUSE_MARKER = '↳';

    private const CAUSE_INDENT = '  ';

    /** Narrow terminals wrap the messages rather than squeeze them into nothing */
    private const MIN_MESSAGE_WIDTH = 40;

    /** Table indentation, the two cell paddings and the closing border */
    private const TABLE_DECORATION_WIDTH = 4;

    public function format(LintReport $report): string
    {
        $output = new BufferedOutput(decorated: true);
        $style = new SymfonyStyle(new ArrayInput([]), $output);

        foreach ($report->getDefects() as $classReport) {
            $this->writeClassReport($style, $classReport);
        }

        $this->writeSummary($style, $report);

        return $output->fetch();
    }

    private function writeClassReport(SymfonyStyle $style, ClassReport $classReport): void
    {
        $defects = $classReport->getDefects();

        $header = $this->headerOf($classReport);
        $width = $this->cellWidthOf($defects, $header);

        $rows = [];

        foreach ($defects as $defect) {
            // A blank line keeps consecutive defects of the same class apart
            $rows[] = [([] === $rows ? '' : "\n").$this->cellOf($defect, $width)];
        }

        $style->createTable()
            ->setHeaders([$header])
            ->setRows($rows)
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
            $width = max($width, Helper::width($this->attributionOf($defect->getLocation())));
        }

        return $width;
    }

    /** The source file, so that the terminal and the IDE can link right to it. */
    private function headerOf(ClassReport $classReport): string
    {
        $filePath = $classReport->getFilePath();

        return null !== $filePath ? $this->relativePath($filePath) : $classReport->getClassName();
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

    private function cellOf(MappingDefect $defect, int $width): string
    {
        return implode("\n", [
            $this->messageOf($defect, $width),
            sprintf('<fg=gray>%s</>', $this->attributionOf($defect->getLocation())),
        ]);
    }

    /** Trails the message the way phpstan trails its own with the file the error belongs to. */
    private function attributionOf(DefectLocation $location): string
    {
        $propertyName = $location->getPropertyName();

        // Class-wide defects belong to no property
        $symbol = null !== $propertyName
            ? sprintf('%s::$%s', $location->getClassName(), $propertyName)
            : $location->getClassName();

        return sprintf('%s  %s', self::LOCATION_MARKER, $symbol);
    }

    /** The root cause leads, and every failure it went on to cause trails it. */
    private function messageOf(MappingDefect $defect, int $width): string
    {
        $messages = $defect->getMessages();

        $lines = [$this->rootCauseOf($defect->getSeverity(), array_shift($messages), $width)];

        foreach ($messages as $depth => $message) {
            $lines[] = $this->consequenceOf($message, $width, $depth + 1);
        }

        return implode("\n", $lines);
    }

    /** Errors carry no label: they are what the linter is expected to report. */
    private function rootCauseOf(DefectSeverity $severity, string $message, int $width): string
    {
        if ($severity->is(DefectSeverity::Error)) {
            return wordwrap($message, $width, "\n", cut_long_words: true);
        }

        $label = $severity->value;

        $wrapped = wordwrap(sprintf('%s: %s', $label, $message), $width, "\n", cut_long_words: true);

        // The label is shorter than the wrapping width, so it always survives it whole and in place
        return substr_replace($wrapped, $this->decorate($label, $severity), 0, strlen($label));
    }

    /** Each consequence nests one level deeper than the failure it was caused by. */
    private function consequenceOf(string $message, int $width, int $depth): string
    {
        $indent = str_repeat(self::CAUSE_INDENT, $depth);
        $offset = Helper::width($indent.self::CAUSE_MARKER.' ');

        $wrapped = wordwrap($message, max(self::MIN_MESSAGE_WIDTH, $width - $offset), "\n", cut_long_words: true);

        return sprintf(
            '%s<fg=gray>%s</> %s',
            $indent,
            self::CAUSE_MARKER,
            // Continuation lines align under the text rather than under the arrow
            str_replace("\n", "\n".str_repeat(' ', $offset), $wrapped),
        );
    }

    private function decorate(string $label, DefectSeverity $severity): string
    {
        $color = match ($severity) {
            DefectSeverity::Error => 'red',
            DefectSeverity::Warning => 'yellow',
            DefectSeverity::Notice => 'blue',
        };

        return sprintf('<fg=%s>%s</>', $color, $label);
    }

    private function writeSummary(SymfonyStyle $style, LintReport $report): void
    {
        $errors = $report->countOf(DefectSeverity::Error);
        $warnings = $report->countOf(DefectSeverity::Warning);

        $summary = sprintf(
            '%d classes scanned: %d errors, %d warnings.',
            $report->getScannedSymbols(),
            $errors,
            $warnings,
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
