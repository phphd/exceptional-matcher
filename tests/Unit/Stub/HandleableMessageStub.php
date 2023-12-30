<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub;

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
}
