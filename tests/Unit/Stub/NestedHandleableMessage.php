<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\NestedPropertyCapturableException;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class NestedHandleableMessage
{
    #[ExceptionalValidation\Capture(NestedPropertyCapturableException::class, 'nested.message')]
    private string $nestedProperty;

    #[Valid]
    private ConditionalMessage $conditionalMessage;

    public static function createWithConditionalMessage(ConditionalMessage $conditionalMessage): self
    {
        $message = new self();
        $message->conditionalMessage = $conditionalMessage;

        return $message;
    }
}
