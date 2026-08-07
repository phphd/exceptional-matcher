<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit\Stub;

use ArrayObject;
use LogicException;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main\Tests\Stub\MessageContainingException;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main\Tests\Stub\ObjectPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\Tests\Stub\SomeValueException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\entos\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\entos\Formatter\Delegating\Tests\Stub\CustomFormattedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\StaticPropertyMatchedException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\embedded_violations;
use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\validated_value;
use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\exception_value;

/**
 * @psalm-suppress InvalidAttribute ("Attribute Catch_ is not repeatable")
 * @psalm-suppress ArgumentTypeCoercion
 */
#[Try_]
final class HandleableMessageStub
{
    #[Catch_(AnException::class, message: 'oops')]
    private int $property;

    #[Catch_(CustomFormattedException::class, format: CustomExceptionViolationFormatter::class, message: 'oops')]
    private string $formatted;

    #[Catch_(ObjectPropertyMatchedException::class, message: 'oops')]
    private object $objectProperty;

    #[Catch_(StaticPropertyMatchedException::class, message: 'oops')]
    private static string $staticProperty = 'foo';

    private NestedHandleableMessage $nestedObject;

    private array $nestedArrayItems; // @phpstan-ignore missingType.iterableValue

    private ArrayObject $nestedIterableItems; // @phpstan-ignore missingType.generics

    #[Catch_(LogicException::class, message: 'oops')]
    private string $messageText;

    // exception_value catch also keeps ConstantsAutoloadingCompilerPassIntegrationTest's constant class in the plan
    #[Catch_(SomeValueException::class, match: exception_value, message: 'oops')]
    #[Catch_(ValidationFailedException::class, match: validated_value, format: embedded_violations)]
    private string $matchedProperty = 'matched!';

    #[Catch_(MessageContainingException::class)]
    private int $fallBackToExceptionMessage;

    #[Catch_(MessageContainingException::class, message: '')]
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
