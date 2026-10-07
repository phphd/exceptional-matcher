<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

use const PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\embedded_violations;

/** @psalm-suppress ArgumentTypeCoercion */
#[Try_]
final class NestedMessageWithEmbeddedViolations
{
    #[Catch_(ViolationsEmbeddedExampleException::class, format: embedded_violations)]
    private int $violationListCapturedProperty;
}
