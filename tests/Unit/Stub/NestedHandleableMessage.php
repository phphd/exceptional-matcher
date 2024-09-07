<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\ViolationListExampleException;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class NestedHandleableMessage
{
    #[ExceptionalValidation\Capture(NestedPropertyCapturableException::class, 'nested.message')]
    private string $nestedProperty;

    #[Valid]
    private ConditionalMessage $conditionalMessage;

    #[ExceptionalValidation\Capture(ViolationListExampleException::class, formatter: ViolationListExceptionFormatter::class)]
    private int $violationListCapturedProperty;

    public static function createWithConditionalMessage(ConditionalMessage $conditionalMessage): self
    {
        $message = new self();
        $message->conditionalMessage = $conditionalMessage;

        return $message;
    }
}
