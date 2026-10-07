<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 *
 * @implements MatchCondition<TException>
 */
final class CatchExceptionMappingNode implements ExceptionMappingNode, MatchCondition
{
    public function __construct(
        private readonly ExceptionMappingNode $property,
        /** @var MatchCondition<TException> */
        private readonly MatchCondition $condition,
        /** @var ?class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly ?string $formatterId,
        private readonly ?string $messageTemplate,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        return $reciprocal->match($this);
    }

    public function getOwner(): ExceptionMappingNode
    {
        return $this->property;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->property->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->property->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->property->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->property->getValue();
    }

    /** @param TException $exception */
    public function matches(Throwable $exception): bool
    {
        return $this->condition->matches($exception);
    }

    /**
     * @internal
     *
     * @return ?class-string<MatchedExceptionFormatter<TException,mixed>>
     */
    public function getFormatterId(): ?string
    {
        return $this->formatterId;
    }

    /** @internal */
    public function getMessageTemplate(): ?string
    {
        return $this->messageTemplate;
    }
}
