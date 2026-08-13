<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter;

/**
 * Checks the `#[Try_]` / `#[Catch_]` mappings of the given classes for every statically detectable error.
 *
 * The reference checks are not re-implemented here: forcing the plan of a class runs the very same
 * compilation the matcher runs in production, only reporting to the defect collector, so the compilers
 * record every mapping they had to drop instead of aborting at the first one. Those records are the
 * defects. The linter only adds the structural observations that the runtime deliberately ignores.
 *
 * @internal
 *
 * @template TSymbol of string
 * @template TReport
 */
interface MappingLinter
{
    /**
     * @param iterable<TSymbol> $symbols
     *
     * @return TReport
     */
    public function lint(iterable $symbols): mixed;
}
