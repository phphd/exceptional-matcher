<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

use const PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\exception_value;

#[Try_]
final class MessageWithExceptionValueCondition
{
    #[Catch_(SomeValueException::class, match: exception_value, message: 'oops')]
    public string $notMatchedProperty;

    #[Catch_(SomeValueException::class, match: exception_value, message: 'oops')]
    public string $matchedProperty;

    #[Catch_(SomeValueException::class, message: 'oops')]
    public string $anotherMatchedAsNoCondition;

    public function __construct(
        string $notMatchedProperty = 'not matched',
        string $matchedProperty = 'matched!',
        string $anotherMatchedAsNoCondition = 'whatever',
    ) {
        $this->notMatchedProperty = $notMatchedProperty;
        $this->matchedProperty = $matchedProperty;
        $this->anotherMatchedAsNoCondition = $anotherMatchedAsNoCondition;
    }
}
