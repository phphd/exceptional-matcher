<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Symfony\Validator\validated_value;
use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\embedded_violations;

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
