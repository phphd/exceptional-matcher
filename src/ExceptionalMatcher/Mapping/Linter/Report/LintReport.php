<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;

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
        $count = 0;

        foreach ($this->defects as $defect) {
            if ($defect->getSeverity()
                ->is($severity)
            ) {
                ++$count;
            }
        }

        return $count;
    }
}
