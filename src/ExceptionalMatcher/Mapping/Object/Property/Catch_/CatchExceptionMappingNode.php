<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\entos\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 */
final class CatchExceptionMappingNode implements ExceptionMappingNode
{
    public function __construct(
        private readonly ExceptionMappingNode $owner,
        /** @var MatchCondition<TException> */
        private readonly MatchCondition $condition,
        /** @var class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly string $formatterId,
        private readonly ?string $messageTemplate,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        $reciprocal->process($this);

        return $reciprocal->isReciprocated();
    }

    public function getOwner(): ExceptionMappingNode
    {
        return $this->owner;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->owner->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->owner->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->owner->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->owner->getValue();
    }

    public function matchesException(Throwable $exception): bool
    {
        return $this->condition->matches($exception);
    }

    /** @return class-string<MatchedExceptionFormatter<TException,mixed>> */
    public function getFormatterId(): string
    {
        return $this->formatterId;
    }

    public function getMessageTemplate(): ?string
    {
        return $this->messageTemplate;
    }
}
