<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Integration\Validator\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\embedded_violations;
use const PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Integration\Validator\validated_value;

#[Try_]
final class MessageWithValidatedValueCondition
{
    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(
        #[Catch_(ValidationFailedException::class, match: validated_value, format: embedded_violations)]
        public string $notMatchedProperty = 'not matched',
        #[Catch_(ValidationFailedException::class, match: validated_value, format: embedded_violations)]
        public string $matchedProperty = 'matched!',
    ) {
    }
}
