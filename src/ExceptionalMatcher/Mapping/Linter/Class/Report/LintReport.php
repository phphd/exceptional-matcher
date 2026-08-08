<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Defect\DefectSeverity;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Defect\MappingDefect;

/** @internal */
final class LintReport
{
    /** @param list<MappingDefect> $defects */
    public function __construct(
        private readonly int $scannedClasses,
        private readonly array $defects,
    ) {
    }

    public function getScannedClasses(): int
    {
        return $this->scannedClasses;
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
