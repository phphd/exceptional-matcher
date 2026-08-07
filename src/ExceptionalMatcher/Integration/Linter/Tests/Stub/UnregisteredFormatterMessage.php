<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class UnregisteredFormatterMessage
{
    #[Catch_(LinterStubException::class, format: UnregisteredFormatter::class, message: 'oops')]
    private ?string $caughtValue = null;
}
