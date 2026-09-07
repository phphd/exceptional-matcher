<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\validated_value;
use const PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\embedded_violations;

/** @psalm-suppress ArgumentTypeCoercion */
#[Try_]
final class MessageWithEmbeddedViolations
{
    #[Catch_(ValidationFailedException::class, match: validated_value, format: embedded_violations)]
    private string $matchedProperty = 'matched!';

    private NestedMessageWithEmbeddedViolations $nestedObject;

    public static function create(): self
    {
        return new self();
    }

    public function withNestedObject(NestedMessageWithEmbeddedViolations $nestedObject): self
    {
        $message = clone $this;
        $message->nestedObject = $nestedObject;

        return $message;
    }
}
