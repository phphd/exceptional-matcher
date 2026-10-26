<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Integrated\Validator\Embedded\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Integrated\Validator\Embedded\embedded_violations;

#[Try_]
final class NestedMessageWithEmbeddedViolations
{
    #[Catch_(ViolationsEmbeddedExampleException::class, format: embedded_violations)]
    private int $violationListCapturedProperty;
}
