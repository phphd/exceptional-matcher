<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter;

/**
 * Checks the `#[Try_]` / `#[Catch_]` mappings correctness.
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
