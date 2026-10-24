<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\Tests\Stub\Invalid;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use RuntimeException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\validated_value;

#[Try_]
final class NonValidationFailedExceptionClassMessage
{
    #[Catch_(RuntimeException::class, match: validated_value)] // @phpstan-ignore argument.type (specifically test the case of missing static analysis)
    public mixed $value;
}
