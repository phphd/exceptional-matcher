<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\Tests\Stub\ViolationListExampleException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\ViolationListExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedPropertyCapturableException;
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
