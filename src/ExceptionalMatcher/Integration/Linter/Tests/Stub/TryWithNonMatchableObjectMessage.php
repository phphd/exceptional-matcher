<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Try_;

/**
 * Declares #[Try_] and even holds a nested object, yet that object's type ({@see NonMatchableObject}) bears
 * no mapping plan, so the compiler produces no ClassMappingPlan and the class can never match anything.
 */
#[Try_]
final class TryWithNonMatchableObjectMessage
{
    private NonMatchableObject $nested;
}
