<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit\Stub;

use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\Tests\Stub\ViolationsEmbeddedExampleException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;
use Symfony\Component\Validator\Constraints\Valid;

use const PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\embedded_violations;

/** @psalm-suppress ArgumentTypeCoercion */
#[Try_]
final class NestedHandleableMessage
{
    #[Catch_(NestedPropertyMatchedException::class, message: 'nested.message')]
    private string $nestedProperty;

    #[Valid]
    private ConditionalMessage $conditionalMessage;

    #[Catch_(ViolationsEmbeddedExampleException::class, format: embedded_violations)]
    private int $violationListCapturedProperty;

    public static function createWithConditionalMessage(ConditionalMessage $conditionalMessage): self
    {
        $message = new self();
        $message->conditionalMessage = $conditionalMessage;

        return $message;
    }
}
