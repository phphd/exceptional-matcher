<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use RuntimeException;
use ValueError;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\enum_value;

/**
 * The broken mapping sits past the emptiness check and its one-plan lookahead, so the property is still
 * planned and the failure only surfaces once the catch plans are traversed.
 *
 * @psalm-suppress InvalidAttribute ("Attribute Catch_ is not repeatable")
 */
#[Try_]
final class LazilyBrokenCatchMessage
{
    /** @psalm-suppress ArgumentTypeCoercion */
    #[Catch_(RuntimeException::class, message: 'first.oops')]
    #[Catch_(LogicException::class, message: 'second.oops')]
    #[Catch_(ValueError::class, match: enum_value)]
    private ?string $caughtValue = null;
}
