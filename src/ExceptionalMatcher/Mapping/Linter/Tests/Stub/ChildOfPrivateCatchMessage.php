<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class ChildOfPrivateCatchMessage extends ParentPrivateCatchMessage
{
    #[Catch_(LinterStubException::class, message: 'oops')]
    private ?string $ownCaughtValue = null;
}
