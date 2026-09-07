<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;

use function array_filter;
use function array_values;
use function count;
use function is_array;
use function iterator_to_array;

/** @internal */
final class LintReport
{
    /** @var list<MappingDefect> */
    private readonly array $defects;

    /** @param iterable<MappingDefect> $defects */
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
