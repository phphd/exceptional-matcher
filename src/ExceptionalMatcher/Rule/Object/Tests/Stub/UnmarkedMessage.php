<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

final class UnmarkedMessage
{
    #[Catch_(PlanStubException::class, message: 'oops')]
    private string $caughtValue;
}
