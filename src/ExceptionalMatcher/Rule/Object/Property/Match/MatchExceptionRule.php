<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;
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
        /** @var class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly string $formatterId,
        private readonly ?string $messageTemplate,
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
