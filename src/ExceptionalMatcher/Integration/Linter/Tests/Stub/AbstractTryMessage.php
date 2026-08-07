<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
abstract class AbstractTryMessage
{
    #[Catch_(LinterStubException::class, message: 'oops')]
    private ?string $caughtValue = null;
}
