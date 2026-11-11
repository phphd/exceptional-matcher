<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;

/**
 * Declares #[Try_] yet has neither #[Catch_] mappings nor any property whose type could hold a
 * matchable object, so the compiler produces no {@see ObjectExceptionMappingBlueprint} and the class can never match.
 */
#[Try_]
final class TryWithNoCatchAttributesMessage
{
    private int $value = 0;
}
