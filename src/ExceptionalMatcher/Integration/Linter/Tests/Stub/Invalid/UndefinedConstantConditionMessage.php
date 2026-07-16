<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\Invalid;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use RuntimeException;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Undefined\undefined_condition;

#[Try_]
final class UndefinedConstantConditionMessage
{
    #[Catch_(RuntimeException::class, match: undefined_condition)]
    private ?string $caughtValue = null;
}
