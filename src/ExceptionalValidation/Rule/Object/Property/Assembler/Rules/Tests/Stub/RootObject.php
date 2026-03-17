<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\Tests\Stub;

use PhPhD\ExceptionalValidation\Rule\Object\Try_;

#[Try_]
final class RootObject
{
    private array $notTypedArray;

    public static function create(): self
    {
        return new self();
    }

    public function withNotTypedArray(array $array): self
    {
        $message = clone $this;
        $message->notTypedArray = $array;

        return $message;
    }
}
