<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use ArrayObject;
use InvalidArgumentException;
use LogicException;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\Tests\Stub\MessageContainingException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\Tests\Stub\ObjectPropertyCapturableException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Validator\ValidationFailedExceptionFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\StaticPropertyCapturedException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/** @template T of object */
#[ExceptionalValidation]
final class HandleableMessageStub
{
    #[ExceptionalValidation\Capture(PropertyCapturableException::class, 'oops')]
    private int $property;

    #[ExceptionalValidation\Capture(CustomFormattedException::class, 'oops', formatter: CustomExceptionViolationFormatter::class)]
    private string $formatted;

    #[ExceptionalValidation\Capture(ObjectPropertyCapturableException::class, 'oops')]
    private object $objectProperty;

    #[ExceptionalValidation\Capture(StaticPropertyCapturedException::class, 'oops')]
    private static string $staticProperty = 'foo';

    private NestedHandleableMessage $ordinaryObject;

    #[Valid]
    private NestedHandleableMessage $nestedObject;

    private array $notTypedArray;

    /** @var NotHandleableMessageStub[] */
    private array $typedNotHandleableArray;

    #[Valid]
    private array $nestedArrayItems;

    #[Valid]
    private ArrayObject $nestedIterableItems;

    #[ExceptionalValidation\Capture(ValidationFailedException::class, from: Email::class)]
    private string $email = 'matched!';

    #[ExceptionalValidation\Capture(InvalidArgumentException::class, from: [Uuid::class, 'fromString'])]
    private string $uid;

    #[ExceptionalValidation\Capture(LogicException::class, 'oops')]
    private string $messageText;

    #[ExceptionalValidation\Capture(SomeValueException::class, 'oops', condition: ExceptionValueMatchCondition::class)]
    #[ExceptionalValidation\Capture(ValidationFailedException::class, condition: ValidationFailedExceptionMatchCondition::class, formatter: ViolationListExceptionFormatter::class)]
    private string $notMatchedProperty = 'not matched';

    #[ExceptionalValidation\Capture(SomeValueException::class, 'oops', condition: ExceptionValueMatchCondition::class)]
    #[ExceptionalValidation\Capture(ValidationFailedException::class, condition: ValidationFailedExceptionMatchCondition::class, formatter: ValidationFailedExceptionFormatter::class)]
    private string $matchedProperty = 'matched!';

    #[ExceptionalValidation\Capture(SomeValueException::class, 'oops')]
    private string $anotherMatchedAsNoCondition;

    #[ExceptionalValidation\Capture(MessageContainingException::class)]
    private int $fallBackToExceptionMessage;

    #[ExceptionalValidation\Capture(MessageContainingException::class, '')]
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

    public function withOrdinaryObject(NestedHandleableMessage $ordinaryObject): self
    {
        $message = clone $this;
        $message->ordinaryObject = $ordinaryObject;

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

    /** @param array<array-key,NestedItem> $array */
    public function withNotTypedArray(array $array): self
    {
        $message = clone $this;
        $message->notTypedArray = $array;

        return $message;
    }

    /** @param NotHandleableMessageStub[] $array */
    public function withTypedNotHandleableArray(array $array): self
    {
        $message = clone $this;
        $message->typedNotHandleableArray = $array;

        return $message;
    }
}
