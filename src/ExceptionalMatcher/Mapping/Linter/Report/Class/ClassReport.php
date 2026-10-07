<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Class;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;
use ReflectionClass;

use function array_filter;
use function array_values;
use function count;

/**
 * The defects a single class was found to have.
 *
 * @internal
 */
final class ClassReport
{
    public function __construct(
        /** @var class-string */
        private readonly string $className,
        /** @var non-empty-list<MappingDefect> */
        private readonly array $defects,
    ) {
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->className;
    }

    /** Path of the file which declares the class, or null when it has no source file. */
    public function getFilePath(): ?string
    {
        $filePath = (new ReflectionClass($this->className))->getFileName();

        return false !== $filePath ? $filePath : null;
    }

    /** @return non-empty-list<MappingDefect> */
    public function getDefects(): array
    {
        return $this->defects;
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
