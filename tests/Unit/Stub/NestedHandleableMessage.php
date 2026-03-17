<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalValidation\Rule\Object\Try_;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;
use PhPhD\ExceptionalValidation\Validator\Formatter\ViolationList\Tests\Stub\ViolationListExampleException;
use PhPhD\ExceptionalValidation\Validator\Formatter\ViolationList\ViolationListExceptionFormatter;
use Symfony\Component\Validator\Constraints\Valid;

#[Try_]
final class NestedHandleableMessage
{
    #[Catch_(NestedPropertyMatchedException::class, 'nested.message')]
    private string $nestedProperty;

    #[Valid]
    private ConditionalMessage $conditionalMessage;

    #[Catch_(ViolationListExampleException::class, formatter: ViolationListExceptionFormatter::class)]
    private int $violationListCapturedProperty;

    public static function createWithConditionalMessage(ConditionalMessage $conditionalMessage): self
    {
        $message = new self();
        $message->conditionalMessage = $conditionalMessage;

        return $message;
    }
}
