<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Path;

use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Path\Discovery\ClassNameDiscovery;

/**
 * @internal
 *
 * @template TReport
 *
 * @implements MappingLinter<string,TReport>
 */
final class ClassPathBasedLinter implements MappingLinter
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
