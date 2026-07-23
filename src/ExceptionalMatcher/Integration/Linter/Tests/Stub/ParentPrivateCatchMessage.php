<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;

/** @psalm-suppress ClassMustBeFinal deliberately extensible - the child inherits its private property */
class ParentPrivateCatchMessage
{
    #[Catch_(LinterStubException::class, message: 'oops')]
    private ?string $parentCaughtValue = null;
}
