<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;

#[Try_]
final class ChildOfPrivateCatchMessage extends ParentPrivateCatchMessage
{
    #[Catch_(LinterStubException::class, message: 'oops')]
    private ?string $ownCaughtValue = null;
}
