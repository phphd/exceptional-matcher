<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\Formatter;

use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;

/**
 * @template TSymbol of string
 * @template TReport
 *
 * @implements MappingLinter<TSymbol,TReport>
 */
interface FormattableMappingLinter extends MappingLinter
{
    public function lint(iterable $symbols, ?string $format = null): mixed;
}
