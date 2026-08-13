<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Path;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Path\Discovery\ClassNameDiscovery;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;

/**
 * @internal
 *
 * @template TReport
 *
 * @implements MappingLinter<string,TReport>
 */
final class ClassNamePathBasedLinter implements MappingLinter
{
    public function __construct(
        private readonly ClassNameDiscovery $classNameDiscovery,
        /** @var MappingLinter<class-string,TReport> */
        private readonly MappingLinter $classNameBasedLinter,
    ) {
    }

    /** @param iterable<string> $symbols */
    public function lint(iterable $symbols): mixed
    {
        $classNames = $this->classNameDiscovery->discover($symbols);

        return $this->classNameBasedLinter->lint($classNames);
    }
}
