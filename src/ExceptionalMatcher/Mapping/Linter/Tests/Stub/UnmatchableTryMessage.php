<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

/**
 * Declares #[Try_] yet has neither #[Catch_] mappings nor any property whose type could hold a
 * matchable object, so the compiler produces no ClassMappingPlan and the class can never match.
 */
#[Try_]
final class UnmatchableTryMessage
{
    private int $value = 0;
}
