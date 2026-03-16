<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match;

use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 */
final class MatchExceptionRule implements MatchingRule
{
    public function __construct(
        private readonly MatchingRule $parent,
        /** @var MatchCondition<TException> */
        private readonly MatchCondition $condition,
        private readonly ?string $messageTemplate,
        /** @var class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly string $formatterId,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        $reciprocal->process($this);

        return $reciprocal->isReciprocated();
    }

    public function getParent(): MatchingRule
    {
        return $this->parent;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->parent->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->parent->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->parent->getValue();
    }

    /** @param TException $exception */
    public function matchesException(Throwable $exception): bool
    {
        return $this->condition->matches($exception);
    }

    public function getMessageTemplate(): ?string
    {
        return $this->messageTemplate;
    }

    /** @return class-string<MatchedExceptionFormatter<TException,mixed>> */
    public function getFormatterId(): string
    {
        return $this->formatterId;
    }
}
