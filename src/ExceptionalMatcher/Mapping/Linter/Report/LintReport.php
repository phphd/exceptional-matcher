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
    public function __construct(
        /** @var list<ClassReport> */
        private readonly array $classReports,
        private readonly int $scannedSymbols,
    ) {
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
    public function getClassReports(): array
    {
        return $this->classReports;
    }

    public function hasDefects(): bool
    {
        return [] !== $this->classReports;
    }

    public function countOf(DefectSeverity $severity): int
    {
        $count = 0;

        foreach ($this->classReports as $classReport) {
            $count += $classReport->countOf($severity);
        }

        return $count;
    }
}
