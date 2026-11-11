<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator\validated_value;
use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\embedded_violations;

#[Try_]
final class MessageWithValidatedValueCondition
{
    public function __construct(
        #[Catch_(ValidationFailedException::class, match: validated_value, format: embedded_violations)]
        public string $notMatchedProperty = 'not matched',
        #[Catch_(ValidationFailedException::class, match: validated_value, format: embedded_violations)]
        public string $matchedProperty = 'matched!',
    ) {
    }
}
