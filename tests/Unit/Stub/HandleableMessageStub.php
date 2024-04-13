<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub;

use ArrayObject;
use LogicException;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\ObjectPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\StaticPropertyCapturedException;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class HandleableMessageStub
{
    #[ExceptionalValidation\Capture(LogicException::class, 'oops')]
    private string $messageText;

    #[ExceptionalValidation\Capture(PropertyCapturableException::class, 'oops')]
    private int $property;

    #[ExceptionalValidation\Capture(ObjectPropertyCapturableException::class, 'object.oops')]
    private object $objectProperty;

    #[ExceptionalValidation\Capture(StaticPropertyCapturedException::class, 'oops')]
    private static string $staticProperty = 'foo';

    private NestedHandleableMessage $ordinaryObject;

    #[Valid]
    private NestedHandleableMessage $nestedObject;

    /** @var array<array-key,NestedItem> */
    #[Valid]
    private array $nestedArrayItems;

    /** @var ArrayObject<array-key,NestedItem> */
    #[Valid]
    private ArrayObject $nestedIterableItems;

    private array $justArray;

    private function __construct()
    {
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createWithMessageText(string $messageText): self
    {
        $message = new self();
        $message->messageText = $messageText;

        return $message;
    }

    public static function createWithObjectProperty(object $objectProperty): self
    {
        $message = new self();
        $message->objectProperty = $objectProperty;

        return $message;
    }

    public static function createWithOrdinaryObject(NestedHandleableMessage $ordinaryObject): self
    {
        $message = new self();
        $message->ordinaryObject = $ordinaryObject;

        return $message;
    }

    public static function createWithNestedObject(NestedHandleableMessage $nestedObject): self
    {
        $message = new self();
        $message->nestedObject = $nestedObject;

        return $message;
    }

    public static function createWithConditionalMessage(int $firstConditionalProperty, int $secondConditionalProperty): self
    {
        return self::createWithNestedObject(NestedHandleableMessage::createWithConditionalMessage(
            ConditionalMessage::createWithConditionalProperties($firstConditionalProperty, $secondConditionalProperty),
        ));
    }

    /** @param array<array-key,NestedItem> $items */
    public static function createWithNestedArrayItems(array $items): self
    {
        $message = new self();
        $message->nestedArrayItems = $items;

        return $message;
    }

    /** @param ArrayObject<array-key,NestedItem> $items */
    public static function createWithNestedIterableItems(ArrayObject $items): self
    {
        $message = new self();
        $message->nestedIterableItems = $items;

        return $message;
    }

    /** @param array<array-key,NestedItem> $justArray */
    public static function createWithJustArray(array $justArray): self
    {
        $message = new self();
        $message->justArray = $justArray;

        return $message;
    }
}
