<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;

use function array_filter;
use function array_values;
use function count;

/** @internal */
final class LintReport
{
    /** @param list<MappingDefect> $defects */
    public function __construct(
        private readonly int $scannedSymbols,
        private readonly array $defects,
    ) {
    }

    public function getScannedSymbols(): int
    {
        return $this->scannedSymbols;
    }

    /** @return list<MappingDefect> */
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
        return count($this->ofSeverity($severity));
    }

    /** @return list<MappingDefect> */
    private function ofSeverity(DefectSeverity $severity): array
    {
        return array_values(array_filter(
            $this->defects,
            static fn (MappingDefect $defect): bool => $defect->getSeverity()
                ->is($severity),
        ));
    }
}
