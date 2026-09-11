<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Class\ClassReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;

use function array_values;
use function is_array;
use function iterator_to_array;

/** @internal */
final class LintReport
{
    /** @var list<ClassReport> */
    private readonly array $defects;

    /** @param iterable<ClassReport> $defects */
    public function __construct(
        private readonly int $scannedSymbols,
        iterable $defects,
    ) {
        $this->defects = is_array($defects)
            ? array_values($defects)
            : iterator_to_array($defects, false);
    }

    public function getScannedSymbols(): int
    {
        return $this->scannedSymbols;
    }

    /**
     * Defects of a single class stay together, in the order their classes were scanned.
     *
     * @return list<ClassReport>
     */
    public function getDefects(): array
    {
        return $this->defects;
    }

    public function hasDefects(): bool
    {
        return [] !== $this->defects;
    }

    public function countOf(DefectSeverity $severity): int
    {
        $count = 0;

        foreach ($this->defects as $classReport) {
            $count += $classReport->countOf($severity);
        }

        return $count;
    }
}
