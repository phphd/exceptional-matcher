<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded\embedded_violations;

#[Try_]
final class NestedMessageWithEmbeddedViolations
{
    #[Catch_(ViolationsEmbeddedExampleException::class, format: embedded_violations)]
    private int $violationListCapturedProperty;
}
