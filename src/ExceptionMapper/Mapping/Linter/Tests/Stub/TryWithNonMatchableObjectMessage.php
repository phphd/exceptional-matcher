<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;

/**
 * Declares #[Try_] and even holds a nested object, yet that object's type ({@see NonMatchableObject}) bears
 * no mapping print, so the compiler produces no {@see ObjectExceptionMappingBlueprint} and the class can never match anything.
 */
#[Try_]
final class TryWithNonMatchableObjectMessage
{
    private NonMatchableObject $nested;
}
