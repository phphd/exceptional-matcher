<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit\Stub;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\Tests\Stub\ViolationListExampleException;
use Symfony\Component\Validator\Constraints\Valid;

use const PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\included_violations;

/** @psalm-suppress ArgumentTypeCoercion */
#[Try_]
final class NestedHandleableMessage
{
    #[Catch_(NestedPropertyMatchedException::class, message: 'nested.message')]
    private string $nestedProperty;

    #[Valid]
    private ConditionalMessage $conditionalMessage;

    #[Catch_(ViolationListExampleException::class, format: included_violations)]
    private int $violationListCapturedProperty;

    public static function createWithConditionalMessage(ConditionalMessage $conditionalMessage): self
    {
        $message = new self();
        $message->conditionalMessage = $conditionalMessage;

        return $message;
    }
}
