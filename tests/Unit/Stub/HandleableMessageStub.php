<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit\Stub;

use ArrayObject;
use InvalidArgumentException;
use LogicException;
use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator\ValidationFailedExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\StaticPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Main\Tests\Stub\MessageContainingException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Main\Tests\Stub\ObjectPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Validator\ValidationFailedExceptionFormatter;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/** @psalm-suppress InvalidAttribute ("Attribute Catch_ is not repeatable") */
#[Try_]
final class HandleableMessageStub
{
    #[Catch_(AnException::class, 'oops')]
    private int $property;

    #[Catch_(CustomFormattedException::class, 'oops', formatter: CustomExceptionViolationFormatter::class)]
    private string $formatted;

    #[Catch_(ObjectPropertyMatchedException::class, 'oops')]
    private object $objectProperty;

    #[Catch_(StaticPropertyMatchedException::class, 'oops')]
    private static string $staticProperty = 'foo';

    private NestedHandleableMessage $nestedObject;

    private array $nestedArrayItems; // @phpstan-ignore missingType.iterableValue

    private ArrayObject $nestedIterableItems; // @phpstan-ignore missingType.generics

    #[Catch_(ValidationFailedException::class, from: Email::class)]
    private string $email = 'matched!';

    #[Catch_(InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
    private string $uid;

    #[Catch_(LogicException::class, 'oops')]
    private string $messageText;

    #[Catch_(SomeValueException::class, 'oops', match: ExceptionValueMatchCondition::class)]
    #[Catch_(ValidationFailedException::class, match: ValidationFailedExceptionMatchCondition::class, formatter: ValidationFailedExceptionFormatter::class)]
    private string $notMatchedProperty = 'not matched';

    #[Catch_(SomeValueException::class, 'oops', match: ExceptionValueMatchCondition::class)]
    #[Catch_(ValidationFailedException::class, match: ValidationFailedExceptionMatchCondition::class, formatter: ValidationFailedExceptionFormatter::class)]
    private string $matchedProperty = 'matched!';

    #[Catch_(SomeValueException::class, 'oops')]
    private string $anotherMatchedAsNoCondition;

    #[Catch_(MessageContainingException::class)]
    private int $fallBackToExceptionMessage;

    #[Catch_(MessageContainingException::class, '')]
    private string $emptyTranslationMessage;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function withMessageText(string $messageText): self
    {
        $message = clone $this;
        $message->messageText = $messageText;

        return $message;
    }

    public function withObjectProperty(object $objectProperty): self
    {
        $message = clone $this;
        $message->objectProperty = $objectProperty;

        return $message;
    }

    public function withNestedObject(NestedHandleableMessage $nestedObject): self
    {
        $message = clone $this;
        $message->nestedObject = $nestedObject;

        return $message;
    }

    public function withConditionalMessage(int $firstConditionalProperty, int $secondConditionalProperty): self
    {
        return $this->withNestedObject(NestedHandleableMessage::createWithConditionalMessage(
            ConditionalMessage::createWithConditionalProperties($firstConditionalProperty, $secondConditionalProperty),
        ));
    }

    /** @param array<array-key,NestedItem> $items */
    public function withNestedArrayItems(array $items): self
    {
        $message = clone $this;
        $message->nestedArrayItems = $items;

        return $message;
    }

    /** @param ArrayObject<array-key,NestedItem> $items */
    public function withNestedIterableItems(ArrayObject $items): self
    {
        $message = clone $this;
        $message->nestedIterableItems = $items;

        return $message;
    }
}
