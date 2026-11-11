<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\exception_value;

#[Try_]
final class MessageWithExceptionValueCondition
{
    #[Catch_(SomeValueException::class, match: exception_value, message: 'oops')]
    public string $notMatchedProperty = 'not matched';

    #[Catch_(SomeValueException::class, match: exception_value, message: 'oops')]
    public string $matchedProperty = 'matched!';

    #[Catch_(SomeValueException::class, message: 'oops')]
    public string $anotherMatchedAsNoCondition = 'whatever';
}
