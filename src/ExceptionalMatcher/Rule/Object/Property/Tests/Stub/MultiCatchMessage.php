<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub;

use DomainException;
use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use RuntimeException;

/** @psalm-suppress InvalidAttribute ("Attribute Catch_ is not repeatable") */
#[Try_]
final class MultiCatchMessage
{
    #[Catch_(RuntimeException::class, message: 'first.oops')]
    #[Catch_(LogicException::class, message: 'second.oops')]
    #[Catch_(DomainException::class, message: 'third.oops')]
    private ?string $caughtValue = null;
}
