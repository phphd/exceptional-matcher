<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use ArrayObject;
use InvalidArgumentException;
use LogicException;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Main\Tests\Stub\MessageContainingException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Main\Tests\Stub\ObjectPropertyCapturableException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Validator\ValidationFailedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\StaticPropertyCapturedException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[ExceptionalValidation]
final class HandleableMessageStub
{
    #[Capture(PropertyCapturableException::class, 'oops')]
    private int $property;

    #[Capture(CustomFormattedException::class, 'oops', formatter: CustomExceptionViolationFormatter::class)]
    private string $formatted;

    #[Capture(ObjectPropertyCapturableException::class, 'oops')]
    private object $objectProperty;

    #[Capture(StaticPropertyCapturedException::class, 'oops')]
    private static string $staticProperty = 'foo';

    private NestedHandleableMessage $nestedObject;

    private array $nestedArrayItems; // @phpstan-ignore missingType.iterableValue

    private ArrayObject $nestedIterableItems; // @phpstan-ignore missingType.generics

    #[Capture(ValidationFailedException::class, from: Email::class)]
    private string $email = 'matched!';

    #[Capture(InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
    private string $uid;

    #[Capture(LogicException::class, 'oops')]
    private string $messageText;

    #[Capture(SomeValueException::class, 'oops', condition: ExceptionValueMatchCondition::class)]
    #[Capture(ValidationFailedException::class, condition: ValidationFailedExceptionMatchCondition::class, formatter: ValidationFailedExceptionFormatter::class)]
    private string $notMatchedProperty = 'not matched';

    #[Capture(SomeValueException::class, 'oops', condition: ExceptionValueMatchCondition::class)]
    #[Capture(ValidationFailedException::class, condition: ValidationFailedExceptionMatchCondition::class, formatter: ValidationFailedExceptionFormatter::class)]
    private string $matchedProperty = 'matched!';

    #[Capture(SomeValueException::class, 'oops')]
    private string $anotherMatchedAsNoCondition;

    #[Capture(MessageContainingException::class)]
    private int $fallBackToExceptionMessage;

    #[Capture(MessageContainingException::class, '')]
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
