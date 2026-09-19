<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Delegating\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class CustomFormattedMessage
{
    #[Catch_(CustomFormattedException::class, format: CustomExceptionViolationFormatter::class, message: 'oops')]
    private ?string $formatted = null;
}
