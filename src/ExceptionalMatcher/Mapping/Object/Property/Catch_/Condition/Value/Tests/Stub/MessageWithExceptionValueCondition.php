<?php /** @noinspection PhpUnused */

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\exception_value;

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
